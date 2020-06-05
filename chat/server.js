const SUCCESS = -2000;
const UNSUCCESS = -2001;
const NONE_REZULT = -2002;
const NOT_CONNECT_TO_DB = -2003;
const USER_NOT_FOUND = -1802;
const UNKNOW_ERROR = -2004
const FAILED_CREATE_ROOM = -2013

var app = require('express')();
const crypto = require('crypto');
const Database = require('./database/DB.js');

var http = require('http').Server(app);
var io = require('socket.io')(http);
var port = process.env.PORT || 3000;

const dbConfig = {
    host: "localhost",
    user: "root",
    database: "ArendaApp",
    password: "12345678"
};

var db = new Database(dbConfig);

app.get('/', function (req, res) {
    res.send("Welcome to my socket");
});

http.listen(port, function () {
    console.log('Server listening at port %d', port);
});

io.on('connection', function (socket) {

    //The moment one of your client connected to socket.io server it will obtain socket id
    //Let's print this out.
    console.log(`Connection : SocketId = ${socket.id}`)
    //Since we are going to use userName through whole socket connection, Let's make it global.   

    socket.on('connectToRoom', function (data) {
        const json = JSON.parse(data)
        const userToken = json.userToken;
        const idUser_To = json.idUser_To;

        console.log(`From json - userToken : ${userToken}; idUser_To : ${idUser_To}`)

        getRoom(userToken, idUser_To).then(
            room => {
                if (room == USER_NOT_FOUND) {
                    console.log("Room is not created")

                    var onError = {
                        codeHandler: USER_NOT_FOUND
                    };

                    socket.emit('onError', onError);
                } else if (room == FAILED_CREATE_ROOM) {
                    console.log("Room is not created")

                    var onError = {
                        codeHandler: FAILED_CREATE_ROOM
                    };

                    socket.emit('onError', onError);
                } else {
                    socket.join(`${room.room}`)

                    console.log(`User token : ${userToken}; idUser_To : ${idUser_To}; room : ${room.room}`)


                    // Let the other user get notification that user got into the room;
                    // It can be use to indicate that person has read the messages. (Like turns "unread" into "read")

                    //TODO: need to chose
                    //io.to : User who has joined can get a event;
                    //socket.broadcast.to : all the users except the user who has joined will get the message
                    // socket.broadcast.to(`${roomName}`).emit('newUserToChatRoom',userName);

                    const response = {
                        idChat: room.idChat
                    };

                    socket.emit('connected', JSON.stringify(response));
                }
            }
        ).catch(error => {
            console.log("Error from catch : " + error.message);

            var onError = {
                error: error.message,
                codeHandler: UNKNOW_ERROR
            };

            socket.emit('onError', onError);

            // db.close();
        });
    })

    socket.on('unsubscribe', function (data) {
        console.log('unsubscribe')
        const messageData = JSON.parse(data);
        const idRoom = messageData.idRoom;

        // socket.to(`${roomName}`).emit('userLeftChatRoom', userName);
        socket.leave(`${idRoom}`);

        // if (io.sockets.clients(idRoom).length == 0)
        //     db.close();
    })

    socket.on('sendedMessage', function (data) {
        console.log('sending Message...');

        const json = JSON.parse(data);
        const content = json.message;
        const idChat = json.idChat;

        const userToken = json.userToken;
        const idUser_To = json.idUser_To;

        console.log(`idChat : ${idChat}; userToken : ${userToken}; content : ${content}`);
        // Just pass the data that has been passed from the writer socket

        insertMessage(idChat, userToken, idUser_To, content).then(response => {
            var message;

            if (response != UNSUCCESS) {
                message = {
                    idMessage: response,
                    message: content,
                    codeHandler: SUCCESS,
                };

                getRoom(userToken, idUser_To).then(
                    room => {
                        if (room == USER_NOT_FOUND) {
                            console.log("Room is not created")

                            var onError = {
                                codeHandler: USER_NOT_FOUND
                            };

                            socket.emit('onError', onError);
                        } else if (room == FAILED_CREATE_ROOM) {
                            console.log("Room is not created")

                            var onError = {
                                codeHandler: FAILED_CREATE_ROOM
                            };

                            socket.emit('onError', onError);
                        } else {
                            socket.emit('sendMessageResponse', JSON.stringify(message));
                            socket.to(`${room.room}`).emit('updateChat', JSON.stringify(message));
                        }
                    }
                ).catch(error => {
                    console.log("Error from catch : " + error.message);

                    var onError = {
                        error: error.message,
                        codeHandler: UNKNOW_ERROR
                    };

                    socket.emit('onError', onError);

                    // db.close();
                });
            } else {
                message = {
                    message: content,
                    codeHandler: response
                };
                socket.emit('sendMessageResponse', message);
            }
        },
            error => {
                const message = {
                    message: content,
                    codeHandler: UNKNOW_ERROR,
                    error: error.message
                };

                console.error(error.message);
                socket.emit('sendMessageResponse', message);
            });
    });

    // socket.on('typing',function(roomNumber){ //Only roomNumber is needed here
    //     console.log('typing triggered')
    //     socket.broadcast.to(`${roomNumber}`).emit('typing')
    // })

    // socket.on('stopTyping',function(roomNumber){ //Only roomNumber is needed here
    //     console.log('stopTyping triggered')
    //     socket.broadcast.to(`${roomNumber}`).emit('stopTyping')
    // })

    socket.on('disconnect', function () {
        console.log("One of sockets disconnected from our server.")
    });
})

function getRoom(userToken, idUser_To) {
    return getIdUser(userToken).then(
        response => {
            if (response.length > 0)
                return response[0].idUser

            return USER_NOT_FOUND;
        }
    ).then(
        idUser_From => {
            if (idUser_From == USER_NOT_FOUND) {
                console.log("User not found");

                return USER_NOT_FOUND;
            } else {
                console.log("idUser_From - " + idUser_From)

                var sql = `SELECT idChat, room FROM chats 
                        WHERE 
                        (idUser_From = '${idUser_From}' OR idUser_From = '${idUser_To}') 
                        AND 
                        (idUser_To = '${idUser_To}' OR idUser_To = '${idUser_From}')`;

                return db.query(sql).then(
                    response => {
                        if (response.length > 0) {
                            console.log("Response from already room - " + response[0]);
                            const room = {
                                idChat: response[0].idChat,
                                room: response[0].room
                            }

                            return room
                        } else {
                            return createRoom(idUser_From, idUser_To).then(room => {
                                console.log("Response from create room - " + room);
                                return room;
                            });
                        }
                    }
                );
            }
        }
    ).then(
        response => {
            // db.close();

            if (response == USER_NOT_FOUND) {
                return USER_NOT_FOUND;
            } else {
                console.log("Room : " + response.room);
                return response;
            }
        }
    );
}

function getIdUser(token) {
    var sql = `SELECT idUser FROM users WHERE token = '${token}'`;
    console.log("Check and get idUser_From...");

    return db.query(sql);
}

function checkIdUser(idUser) {
    var sql = `SELECT idUser FROM users WHERE idUser = '${idUser}'`;
    console.log("Check idUser...");

    return db.query(sql);
}


function createRoom(idUser_From, idUser_To) {
    var sql = `INSERT INTO chats (idUser_From, idUser_To, room) VALUE 
                ('${idUser_From}', '${idUser_To}', '${generateMD5Hex(idUser_From, idUser_To)}')`;

    var sqlSelectRoom = `SELECT idChat, room FROM chats 
                        WHERE 
                        (idUser_From = '${idUser_From}' OR idUser_From = '${idUser_To}') 
                        AND 
                        (idUser_To = '${idUser_To}' OR idUser_To = '${idUser_From}')`;
    console.log("Create chat room...");

    return db.query(sql).then(response => {
        if (response.insertId > 0)
            return db.query(sqlSelectRoom).then(
                response => {
                    const room = {
                        idChat: response[0].idChat,
                        room: response[0].room
                    }

                    return room;
                }
            );
        else
            return FAILED_CREATE_ROOM;
    });
}

function insertMessage(idChat, userToken, idUser_To, message) {
    return getIdUser(userToken).then(
        response => {
            if (response.length > 0)
                return response[0].idUser

            return USER_NOT_FOUND;
        }
    ).then(
        idUser_From => {
            if (idUser_From == USER_NOT_FOUND)
                return USER_NOT_FOUND;
            else {
                console.log("Insert message...");

                var sql = `INSERT INTO messages (idChat, idUser_From, idUser_To, message) VALUE (${idChat}, ${idUser_From}, ${idUser_To}, '${message}')`;
                return db.query(sql).then(
                    response => {
                        if (response.affectedRows > 0) {
                            console.log("SUCCESS");
                            return response.insertId;
                        } else {
                            console.log("UNSUCCESS");
                            return UNSUCCESS;
                        }
                    }
                )
            }
        }
    );
}

function random(min, max) {
    const q = Math.random() * (max - min) + min;
    console.log(q)
    return q;
}

function generateMD5Hex(f, s) {
    return crypto.createHash('md5').update(`${f}` + random(s, f) + `${s}`).digest('hex');
}