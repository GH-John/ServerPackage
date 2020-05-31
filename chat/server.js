const USER_NOT_FOUND = -1802;
const NONE_REZULT = -2002;
const NOT_CONNECT_TO_DB = -2003;

var app = require('express')();
const mysql = require('mysql2')

var http = require('http').Server(app);
var io = require('socket.io')(http);
var port = process.env.PORT || 3000;

const db = mysql.createConnection({
    host: "localhost",
    user: "root",
    database: "ArendaApp",
    password: "12345678"
})

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

        getIdRoom(userToken, idUser_To).then(
            idRoom => {
                if (idRoom == USER_NOT_FOUND) {
                    console.log("Room is not created")
                } else {
                    socket.join(`${idRoom}`)

                    console.log(`User token : ${userToken}; idUser_To : ${idUser_To}; idRoom : ${idRoom}`)


                    // Let the other user get notification that user got into the room;
                    // It can be use to indicate that person has read the messages. (Like turns "unread" into "read")

                    //TODO: need to chose
                    //io.to : User who has joined can get a event;
                    //socket.broadcast.to : all the users except the user who has joined will get the message
                    // socket.broadcast.to(`${roomName}`).emit('newUserToChatRoom',userName);

                    io.to(`${idRoom}`).emit('connected', idRoom);
                }
            }
        );
    })

    socket.on('unsubscribe', function (data) {
        console.log('unsubscribe trigged')
        const room_data = JSON.parse(data);
        const userName = room_data.userName;
        const roomName = room_data.roomName;

        console.log(`Username : ${userName} leaved Room Name : ${roomName}`);
        socket.broadcast.to(`${roomName}`).emit('userLeftChatRoom', userName);
        socket.leave(`${roomName}`);
    })

    socket.on('newMessage', function (data) {
        console.log('newMessage triggered');

        const messageData = JSON.parse(data);
        const messageContent = messageData.messageContent;
        const roomName = messageData.roomName;

        const idUser_From = messageData.idUser_From;
        const idUser_To = messageData.idUser_To;

        console.log(`[Room Number ${roomName}] ${userName} : ${messageContent}`);
        // Just pass the data that has been passed from the writer socket

        insertMessage(idUser_From, idUser_To, messageContent).then(response => {
            const chatData = {
                userName: userName,
                messageContent: messageContent,
                roomName: roomName
            };
            socket.broadcast.to(`${roomName}`).emit('updateChat', JSON.stringify(chatData)); // Need to be parsed into Kotlin object in Kotlin
        },
            error => {
                socket.broadcast.to(`${roomName}`).emit('errorInsertMessage', error); // Need to be parsed into Kotlin object in Kotlin
            })
    })

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
                console.log("User is founded : " + idUser_From);

                var sql = `SELECT idRoom FROM chatRoom 
                        WHERE 
                        (idUser_From = '${idUser_From}' OR idUser_From = '${idUser_To}') 
                        AND 
                        (idUser_To = '${idUser_To}' OR idUser_To = '${idUser_From}')`;

                runQuery(sql).then(
                    response => {
                        if (response.length > 0) {
                            return response[0].idRoom
                        } else {
                            createChatRoom(idUser_From, idUser_To).then(response => {
                                if (response.length > 0)
                                    return response.insertId
                            });
                        }
                    }
                );
            }
        }
    ).then(
        response => {
            if (response == USER_NOT_FOUND) {
                return USER_NOT_FOUND;
            } else {
                console.log("Room id : " + response);
                return response;
            }
        }
    ).catch(error => console.log("Error from catch : " + error.message))
}

function getIdUser(token) {
    var sql = `SELECT idUser FROM users WHERE token = '${token}'`;
    console.log("Check and get idUser...");

    return runQuery(sql);
}

function createChatRoom(idUser_From, idUser_To) {
    var sql = `INSERT INTO chatRoom (idUser_From, idUser_To) VALUE ('${idUser_From}', '${idUser_To}')`;
    console.log("Create chat room...");

    return runQuery(sql);
}

function insertMessage(idUser_From, idUser_To, message) {
    var sql = `INSERT INTO messages (idUser_From, idUser_To, message) VALUE (${idUser_From}, ${idUser_To}, '${message}')`;
    console.log("Insert message...");

    return runQuery(sql);
}

function runQuery(sql) {
    return new Promise(function (resolve, reject) {
        db.connect(function (error) {
            if (error) {
                reject(error);
                console.error("Connect error: " + error.message);
            }
            else {
                console.log("MySQL connect is success");

                db.query(sql, function (error, result) {
                    if (error) {
                        reject(error);
                        console.error("Error: " + error.message);
                    } else {
                        db.end(function (error) {
                            if (error) {
                                reject(error);
                                console.error("Error: " + error.message);
                            } else {
                                console.log("MySQL connect is closed");
                                console.log("Query result : " + result);
                                resolve(result);
                            }
                        });
                    }
                });
            }
        });
    });
}

//Result Object from mysql
// {
    // fieldCount: 0,
    // affectedRows: 1,
    // insertId: 0,
    // serverStatus: 34,
    // warningCount: 0,
    // message: '(Rows matched: 1 Changed: 1 Warnings: 0',
    // protocol41: true,
    // changedRows: 1
// }