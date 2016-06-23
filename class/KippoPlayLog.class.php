<?php

class KippoPlayLog
{
    private $db_conn;

    function __construct()
    {
        //Let's connect to the database
        $this->db_conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT); //host, username, password, database, port

        if (mysqli_connect_errno()) {
            echo 'Error connecting to the database: ' . mysqli_connect_error();
            exit();
        }
    }

    function __destruct()
    {
        $this->db_conn->close();
    }

    public function printLogs()
    {
        $db_query = "SELECT * "
            . "FROM ( "
            . "SELECT ttylog.session, timestamp,  "
            . "ROUND(LENGTH(ttylog)/1024, 2) AS size  "
            . "FROM ttylog "
            . "JOIN auth "
            . "ON ttylog.session = auth.session "
            . "WHERE auth.success = 1 "
            . "AND `timestamp` > DATE_SUB(now(), interval ".LAST_WEEKS." week) "
            . "ORDER BY timestamp DESC "
            . ") s "
            . "WHERE size > " . PLAYBACKSIZE_IGNORE;

        $result = $this->db_conn->query($db_query);
        //echo 'Found '.$result->num_rows.' records';

        if ($result->num_rows > 0) {
            //We create a skeleton for the table
            $counter = 1;
            echo '<p>The following table displays a list of all the logs recorded by Kippo.</p>';
            echo '<table><thead>';
            echo '<tr class="dark">';
            echo '<th>ID</th>';
            echo '<th>Timstamp</th>';
            echo '<th>Size</th>';
            echo '<th>Play the log</th>';
            echo '</tr></thead><tbody>';

            //For every row returned from the database we create a new table row with the data as columns
            while ($row = $result->fetch_array(MYSQLI_BOTH)) {
                echo '<tr class="light word-break">';
                echo '<td>' . $counter . '</td>';
                echo '<td>' . $row['timestamp'] . '</td>';
                echo '<td>' . $row['size'] . 'kb' . '</td>';
                echo '<td><a href="include/play.php?f=' . $row['session'] . '" target="_blank"><img class="icon" src="images/play.ico"/>Play</a></td>';
                echo '</tr>';
                $counter++;
            }

            //Close tbody and table element, it's ready.
            echo '</tbody></table>';
            echo '<hr /><br />';
        }
    }

}

?>
