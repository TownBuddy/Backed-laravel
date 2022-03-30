var app = require('express')();
var http = require('http').Server(app);
var io = require('socket.io')(http);
var mysql = require('mysql');
var moment = require('moment-timezone');
const axios = require('axios');

var db = require('./database.js');

// Database connection
var conn = mysql.createPool(db.myModule());

var users = [];
let connections=[];

http.listen(8001, function () {
    console.log('Listening to port 8001');
	//console.log(io);
});


conn.getConnection(function(err,connection){
	if(err) throw err;
	var sql1 = "truncate user_socket";
	//3 excute the query
	connection.query(sql1,function(err, result, fields){
		console.log('users sockets deleted');
	});
	
	console.log('db connected as id '+connection.threadId);
	
	//2. Build the query
	//var datetime = moment().format('MMMM Do YYYY, hh:mm:ss');
    	//console.log(datetime);
});

io.on('connection', function (socket) {
	console.log('Connection');
	
	socket.on("user_connected", function (user_id) {
        	users[user_id] = socket.id;
		connections.push(socket);
        	io.emit('updateUserStatus', users);
        	console.log("user connected "+ user_id);
		
		try{
			let sqlchek = "SELECT * FROM user_socket WHERE user_id='" + user_id + "'";
			conn.query(sqlchek,function(err,row,fields){
				if(row.length == 0){
					let sql = "INSERT INTO `user_socket` (`user_id`, `socket_id`) VALUES ('" + user_id + "', '" + socket.id + "')";
					conn.query(sql,function(err,row,fields){});
				}else{
					let sql2 = "UPDATE user_socket SET socket_id = '"+socket.id+"' WHERE user_id = '"+user_id+"'";
					conn.query(sql2,function(err,row,fields){});
				}
			});
			
			
		}catch(err){
			throw err;
		}
    });
	
	socket.on("send_message", function (object) {
        console.log(object);
        //object = JSON.parse(object);
		//let data = object.data;
		
        let sender_id = object.sender_id;
        let receiver_id = object.receiver_id;
		let deal_id = object.deal_id;
        let message = object.message;
		let datetime = moment().tz("Asia/Kolkata").format('Y-MM-D H:mm:ss')
		
		//console.log("sender_id - "+ sender_id);
		//console.log("receiver_id - "+ receiver_id);
		//console.log("message - "+ message);
		console.log("Sender:"+sender_id+" Receiver:"+receiver_id+" Deal id:"+deal_id+" Message: "+ message);
		
		try{
			let sql = "INSERT INTO `messages` (`sender_id`, `receiver_id`,`deal_id`, `message`, `msg_time`, `created_at`, `updated_at`) VALUES ('" + sender_id + "', '" + receiver_id + "', '"+ deal_id +"' , '" + message + "', '" + datetime + "', '" + datetime + "', '" + datetime + "')";
			//console.log("sql - "+ sql);
			conn.query(sql,function(err,row,fields){});
			
			//Send Chat Notification
			const req_data = {
                sender_id: sender_id,
                receiver_id: receiver_id,
                deal_id:deal_id,
                message:message
            };
            
            axios.post('http://townbuddytravel.com/api/send-chat-notification', req_data)
                .then((res) => {
                    //console.log(res);
                    //console.log('Body: ', res.data);
                }).catch((err) => {
                    //console.error(err);
                });
			if (typeof users[receiver_id] === "undefined") {
                // User Socket Not Register
                //console.log('Not Regggg');
            }
            else {
                //console.log('Regggg');
                io.to(`${users[receiver_id]}`).emit("recieve_message", object);
            }
			
			
		}catch(err){
			throw err;
		}
		
    });
	
    	socket.on('disconnect', function() {
        	console.log('On Disconnect..');
        	var i = users.indexOf(socket.id);
		if(i >= 0 ){
			users.splice(i, 1, 0);
			io.emit('updateUserStatus', users);
	   	}
        	console.log(users);
    	});
	
	
	// Group Chat Sockets
	
	//Join Room
	socket.on("join_room", function (object) {
        //console.log(object);
		
		let user_id = object.user_id;
        	let room_id = object.room_id;
		
		users[user_id] = socket.id;
		connections.push(socket);
		socket.join("room_"+room_id);
		
		console.log('User '+user_id+' Join Room room_'+room_id);
		console.log(socket.rooms);
		//socket.to("room_"+room_id).emit("group_message",object );
    	});
	
	socket.on("group_message", function (object) {
        //console.log(object);
		
		let user_id = object.user_id;
        	let room_id = object.room_id;
        	let message = object.message;
		
		console.log('User '+user_id+' Room room_'+room_id+' Message '+message);
		io.to("room_"+room_id).emit("recieve_group_message",object );
    	});	
});
