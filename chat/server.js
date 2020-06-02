const SUCCESS = -2000;
const UNSUCCESS = -2001;
const NONE_REZULT = -2002;
const NOT_CONNECT_TO_DB = -2003;
const USER_NOT_FOUND = -1802;
const UNKNOW_ERROR = -2004

var app = require('express')();
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

        getIdRoom(userToken, idUser_To).then(
            idRoom => {
                if (idRoom == USER_NOT_FOUND) {
                    console.log("Room is not created")

                    socket.emit('USER_NOT_FOUND', USER_NOT_FOUND);
                } else {
                    socket.join(`${idRoom}`)

                    console.log(`User token : ${userToken}; idUser_To : ${idUser_To}; idRoom : ${idRoom}`)


                    // Let the other user get notification that user got into the room;
                    // It can be use to indicate that person has read the messages. (Like turns "unread" into "read")

                    //TODO: need to chose
                    //io.to : User who has joined can get a event;
                    //socket.broadcast.to : all the users except the user who has joined will get the message
                    // socket.broadcast.to(`${roomName}`).emit('newUserToChatRoom',userName);

                    const response = {
                        idRoom: idRoom
                    };

                    io.to(`${idRoom}`).emit('connected', JSON.stringify(response));
                }
            }
        ).catch(error => {
            console.log("Error from catch : " + error.message);
            // db.close();
        });
    })

    // socket.on('unsubscribe', function (data) {
    //     console.log('unsubscribe trigged')
    //     const room_data = JSON.parse(data);
    //     const userName = room_data.userName;
    //     const roomName = room_data.roomName;

    //     console.log(`Username : ${userName} leaved Room Name : ${roomName}`);
    //     socket.broadcast.to(`${roomName}`).emit('userLeftChatRoom', userName);
    //     socket.leave(`${roomName}`);
    // })

    socket.on('sendedMessage', function (data) {
        console.log('sendedMessage...');

        const messageData = JSON.parse(data);
        const content = messageData.message;
        const idRoom = messageData.idRoom;

        const userToken = messageData.userToken;
        const idUser_To = messageData.idUser_To;

        console.log(`idRoom : ${idRoom}; userToken : ${userToken}; content : ${content}`);
        // Just pass the data that has been passed from the writer socket

        insertMessage(idRoom, userToken, idUser_To, content).then(response => {
            var message;

            if (response == SUCCESS) {
                message = {
                    message: content,
                    codeHandler: response
                };

                socket.broadcast.to(`${idRoom}`).emit('updateChat', JSON.stringify(message));
            } else {
                message = {
                    message: content,
                    codeHandler: response
                };
                socket.emit('sendMessageError', message);
            }
        },
            error => {
                const message = {
                    message: content,
                    codeHandler: UNKNOW_ERROR,
                    error: error.message
                };

                console.error(error.message);
                socket.emit('sendMessageError', message);
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
        if (io.sockets.clients('room').length == 0)
            db.close();

        console.log("One of sockets disconnected from our server.")
    });
})

function getIdRoom(userToken, idUser_To) {
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

                var sql = `SELECT idRoom FROM chatRoom 
                        WHERE 
                        (idUser_From = '${idUser_From}' OR idUser_From = '${idUser_To}') 
                        AND 
                        (idUser_To = '${idUser_To}' OR idUser_To = '${idUser_From}')`;

                return db.query(sql).then(
                    response => {
                        if (response.length > 0) {
                            console.log("Response from already room - " + response);
                            return response[0].idRoom
                        } else {
                            return createChatRoom(idUser_From, idUser_To).then(response => {
                                console.log("Response from create room - " + response);
                                return response.insertId
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
                console.log("Room id : " + response);
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


function createChatRoom(idUser_From, idUser_To) {
    var sql = `INSERT INTO chatRoom (idUser_From, idUser_To) VALUE ('${idUser_From}', '${idUser_To}')`;
    console.log("Create chat room...");

    return db.query(sql);
}

function insertMessage(idRoom, userToken, idUser_To, message) {
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

                var sql = `INSERT INTO messages (idRoom, idUser_From, idUser_To, message) VALUE (${idRoom}, ${idUser_From}, ${idUser_To}, '${message}')`;
                return db.query(sql).then(
                    response => {
                        if (response.affectedRows > 0) {
                            console.log("SUCCESS");
                            return SUCCESS;
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