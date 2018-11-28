<?php
//11-01-2018
//started on 01-06-2017
// La app di Heroku si puo richiamare da browser con
//			https://salottozie.herokuapp.com/


/*API key = 403019908:AAEiOdP8dRt8OXsoRAws4d01AwolZ7TNXYw

da browser request ->   https://salottozie.herokuapp.com/register.php
           answer  <-   {"ok":true,"result":true,"description":"Webhook is already set"}
In questo modo invocheremo lo script register.php che ha lo scopo di comunicare a Telegram
l’indirizzo dell’applicazione web che risponderà alle richieste del bot.

da browser request ->   https://api.telegram.org/bot403019908:AAEiOdP8dRt8OXsoRAws4d01AwolZ7TNXYw/getMe
           answer  <-   {"ok":true,"result":{"id":403019908,"is_bot":true,"first_name":"salottozie","username":"salottozie_bot"}}

riferimenti:
https://gist.github.com/salvatorecordiano/2fd5f4ece35e75ab29b49316e6b6a273
https://www.salvatorecordiano.it/creare-un-bot-telegram-guida-passo-passo/
*/
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if(!$update)
{
  exit;
}

$message = isset($update['message']) ? $update['message'] : "";
$messageId = isset($message['message_id']) ? $message['message_id'] : "";
$chatId = isset($message['chat']['id']) ? $message['chat']['id'] : "";
$firstname = isset($message['chat']['first_name']) ? $message['chat']['first_name'] : "";
$lastname = isset($message['chat']['last_name']) ? $message['chat']['last_name'] : "";
$username = isset($message['chat']['username']) ? $message['chat']['username'] : "";
$date = isset($message['date']) ? $message['date'] : "";
$text = isset($message['text']) ? $message['text'] : "";

// pulisco il messaggio ricevuto togliendo eventuali spazi prima e dopo il testo
$text = trim($text);
// converto tutti i caratteri alfanumerici del messaggio in minuscolo
$text = strtolower($text);

header("Content-Type: application/json");

//ATTENZIONE!... Tutti i testi e i COMANDI contengono SOLO lettere minuscole
$response = '';
$helptext = "List of commands :
---CONVETTORI--- 
/on_on    -> Salotto ON  Letto ON
/son_boff -> Salotto ON  Letto OFF  
/soff_bon -> Salotto OFF Letto ON
/off_off  -> Salotto OFF Letto OFF
/salotto  -> Lettura stazione4 ... su bus RS485
";

if(strpos($text, "/start") === 0 || $text=="ciao" || $text == "help"){
	$response = "Ciao $firstname, benvenuto   \n". $helptext; 
}

//<-- Comandi ai rele
elseif($text=="/on_on"){
	$response = file_get_contents("http://dario95.ddns.net:8083/rele/4/3");
}
elseif($text=="/son_boff"){
	$response = file_get_contents("http://dario95.ddns.net:8083/rele/4/2");
}
elseif($text=="/soff_bon"){
	$response = file_get_contents("http://dario95.ddns.net:8083/rele/4/1");
}
elseif(strpos($text,"/off_off")){
	$response = file_get_contents("http://dario95.ddns.net:8083/rele/4/0");
}
//<-- Lettura parametri slave4
elseif($text=="/salotto"){
	$response = file_get_contents("http://dario95.ddns.net:8083/salotto");
}

//<-- Manda a video la risposta completa
elseif($text=="/verbose"){
	$response = "chatId ".$chatId. "   messId ".$messageId. "  user ".$username. "   lastname ".$lastname. "   firstname ".$firstname . "\n". $helptext ;		
  $response = $response. "\n\n Heroku + dropbox libero.it";	
}


else
{
	$response = "Unknown command!";			//<---Capita quando i comandi contengono lettere maiuscole
}

// la mia risposta è un array JSON composto da chat_id, text, method
// chat_id mi consente di rispondere allo specifico utente che ha scritto al bot
// text è il testo della risposta
$parameters = array('chat_id' => $chatId, "text" => $response);
$parameters["method"] = "sendMessage";
// imposto la keyboard
$parameters["reply_markup"] = '{ "keyboard": [["/on_on", "/son_boff"],["/soff_bon", "/off_off \ud83d\udd35"],["/salotto"]], "one_time_keyboard": false, "resize_keyboard": true}';
// converto e stampo l'array JSON sulla response
echo json_encode($parameters);
?>