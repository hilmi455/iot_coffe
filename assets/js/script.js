const broker = "broker.hivemq.com";
const port = 8884;
const path = "/mqtt"
const topic = "coffee/sortasi";

const client =
new Paho.MQTT.Client(
    broker,
    Number(port),
    path,
    "web_" + Math.random().toString(16).substr(2)
);

client.onConnectionLost = function(response){

    console.log("MQTT Disconnect");

    if(response.errorCode !== 0){

        console.log(response.errorMessage);

    }

};

client.onMessageArrived = function(message){

    try{

        const data = JSON.parse(message.payloadString);

        console.log(data);

    }catch(e){

        console.log(message.payloadString);

    }

};

client.connect({

    useSSL:true,

    onSuccess:function(){

        console.log("MQTT Connected");

        client.subscribe(topic);

    },

     onFailure:function(error){

        console.log("MQTT Failed");

        console.log(error);

    }

});