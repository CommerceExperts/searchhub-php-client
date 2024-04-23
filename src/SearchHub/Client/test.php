<?php

namespace SearchHub\Client;

use SearchHub\Client\SearchHubRequest;

    While (True) {
        echo "Was möchten Sie kaufen?\n";
        $input = strval(fgets(STDIN));
        $userQuery = substr_replace(preg_replace('/^\s+/', '', $input), "", -2); //Entwerfen alle unnötige " " und "\n"
        


        if (!rtrim($userQuery, " ")){ //Cheak, ob etwas gegeben wurde
            echo "Sie haben nichts gegeben. Versuchen Sie noch mal\n";
        } else{
            if ($userQuery == "exit"){
                echo "Danke auf Ihre Zeit";
                break;
            } 
            else{ 
                    echo("Sie haben $userQuery gesucht!\n");

                    $SearchRequest = new SearchHubRequest($userQuery);
                    $Client = new SearchHubClient();
                
                    $result = $Client->optimize($SearchRequest);
                    echo($result);

                }
            }
         
    }
    

?>
