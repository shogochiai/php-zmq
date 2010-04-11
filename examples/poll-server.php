<?php

/* Create socket, request-reply pattern (reply socket) */
$server = new ZMQ(ZMQ::SOCKET_REP);

/* Set 1 app thread, 1 io thread and turn poll support on */
$server->setContextOptions(1, 1, true)

/* Bind to port 5555 on 127.0.0.1 */
$server->bind("tcp://127.0.0.1:5555");

/* Create new pollset for incoming/outgoing message */
$poll = new ZMQPoll();

/* Add the object and listen for poll in/out */
$id = $poll->add($server, ZMQ::POLL_IN | ZMQ::POLL_OUT);
echo "Added object with id " . $id . "\n";

/* Initialise readable and writable arrays */
$readable = array();
$writable = array();

while (true) {
    /* Amount of events retrieved */
    $events = 0;
    
    try {
        /* Poll until there is something to do */
        $events = $poll->poll($readable, $writable, -1);
    } catch (ZMQPollException $e) {
        echo "poll failed: " . $e->getMessage() . "\n";
    }
    
    if ($events > 0) {
        /* Loop through readable objects and recv messages */
        foreach ($readable as $r) {
            try {
                echo "Received message: " . $r->recv() . "\n";
            } catch (ZMQException $e) {
                echo "recv failed: " . $e->getMessage() . "\n";
            }
        }
        
        /* Loop through writable and send back messages */
        foreach ($writable as $w) {
            try {
                $w->send("Got it!");
            } catch (ZMQException $e) {
                echo "send failed: " . $e->getMessage() . "\n";
            }
        }
    }
}
