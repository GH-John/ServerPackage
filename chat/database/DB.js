const mysql = require('mysql2');

class Database {
    constructor(config) {
        this.refreshConnect(config)
    }
    refreshConnect(config) {
        this.connection = mysql.createConnection(config);
        console.log("Database connection created")
    }
    query(sql, args) {
        return new Promise((resolve, reject) => {
            this.connection.query(sql, args, (err, rows) => {
                if (err)
                    return reject(err);
                resolve(rows);
            });
        });
    }
    close() {
        return new Promise((resolve, reject) => {
            this.connection.end(err => {
                if (err) {
                    console.error("Database connect error : " + err.message)
                    return reject(err);
                }

                console.log("Database connect closed")
                resolve();
            });
        });
    }
}
//Example result Object from response
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
module.exports = Database