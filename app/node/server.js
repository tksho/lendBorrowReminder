var fs = require('fs');
var io = require('socket.io').listen(8000, {
	key : fs.readFileSync('/etc/letsencrypt/live/bot-6415.chat-ai.tk/privkey.pem').toString(),
	cert: fs.readFileSync('/etc/letsencrypt/live/bot-6415.chat-ai.tk/cert.pem').toString(),
	//ca: fs.readFileSync('/etc/letsencrypt/live/bot-6415.chat-ai.tk/chain.pem').toString(),
});
var ioredis = require('socket.io-redis');
var redis = require('redis');
var adapter = io.adapter(ioredis({ host : '127.0.0.1', port: 6379 }));
var sto = redis.createClient(6379, 'localhost');



//       var pub = redis.createClient(6379, 'localhost');
//       //        var sub = redis.createClient(6379, 'localhost')
//
io.set('heartbeat interval', 5000);
io.set('heartbeat timeout', 15000);
//io.set('origins', 'https://bot-6415.chat-ai.tk:80');
io.origins('*:*');
console.log("started");
io.on('connection', function (socket) {
	console.log("connected");

	socket.on('message', function(data) {
		io.emit('message', data);
	});

	socket.on('disconnect', function(data) {
		console.log('disconnected');
	});
});
