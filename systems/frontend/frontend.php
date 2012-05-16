<?php
/**
 * Frontend class file.
 *
 * @author Chris Smith <dmagick@gmail.com>
 * @version 1.0
 * @package money
 */

/**
 * The frontend class.
 * Works out which page you are trying to view and processes it.
 * Could hand off requests to other systems if it needs to.
 *
 * @package money
 */
class frontend
{

    /**
     * Display a page.
     *
     * If the user hasn't logged in, it remembers the page you are trying
     * to view, takes you to the login page, then if that works, redirects
     * the user back to the original page.
     *
     * @return void
     *
     * @uses isValidSystem
     * @uses session::get
     * @uses session::has
     * @uses session::remove
     * @uses session::set
     * @uses template::display
     * @uses template::serveTemplate
     * @uses user::process
     */
    public static function display()
    {
        $page = '';
        if (isset($_SERVER['PATH_INFO']) === TRUE) {
            $page = trim($_SERVER['PATH_INFO'], '/');
        }

        if (session::has('user') === FALSE) {
            if (session::has('viewPage') === FALSE) {
                session::set('viewPage', $page);
            }
            user::process();
            return;
        }

        if (session::has('viewPage') === TRUE) {
            $page = session::get('viewPage');
            session::remove('viewPage');
        }

        if (empty($page) === FALSE) {
            $info = trim($page, '/');
            $bits = explode('/', $info);
            if (empty($bits[0]) === FALSE) {
                $system = array_shift($bits);

                if ($system !== 'frontend') {
                    template::serveTemplate('header');
                    template::display();
                }

                $bits   = implode('/', $bits);
                if (isValidSystem($system) === TRUE) {
                    call_user_func_array(array($system, 'process'), array($bits));
                }
            }
        } else {
            template::serveTemplate('header');
            template::display();
            $transactionInfo = account_transaction::getTransactions();
            if (empty($transactionInfo) === TRUE) {
                template::serveTemplate('home.empty');
            } else {
                session::set('transactionInfo', $transactionInfo);
                template::serveTemplate('home');
            }
        }

        template::serveTemplate('footer');
        template::display();
    }

    public static function process($action=NULL)
    {
        switch ($action) {
            case 'graph':
                self::showGraph();
            break;
            default:
                exit;
        }
    }

    public static function showGraph()
    {
        require dirname(dirname(__FILE__)).'/jpgraph/src/jpgraph.php';
        require dirname(dirname(__FILE__)).'/jpgraph/src/jpgraph_line.php';

        try {
            $transactionInfo = session::get('transactionInfo');
        } catch (Exception $e) {
            $transactionInfo = account_transaction::getTransactions();
        }

        /**
         * Transaction info is done in descending order, first we need to
         * reverse that so we get the first transaction.
         * This will be shown on the left.
         */
        $transactionInfo = array_reverse($transactionInfo);

        $accounts    = array();
        $accountInfo = array();
        $timeStamps  = array();
        foreach ($transactionInfo as $_k => $transactionData) {
            $accountId = $transactionData['account_id'];
            if (isset($accounts[$accountId]) === FALSE) {
                $accounts[$accountId]    = array();
                $accountInfo[$accountId] = array(
                                            'name'   => $transactionData['account_name'],
                                            'number' => $transactionData['account_number'],
                                           );
            }
            $accounts[$accountId][] = $transactionData['account_balance_new'];
            $timeStamps[]           = date('Y-m-d H:i', $transactionData['transaction_date']);
        }

        // Width and height of the graph
        $width  = 600;
        $height = 500;

        // Create a graph instance
        $graph = new Graph($width,$height);

        // Specify what scale we want to use,
        // int = integer scale for the X-axis
        // int = integer scale for the Y-axis
        $graph->SetScale('intlin');

        // Setup a title for the graph
        $graph->title->Set('Transactions Over Time');

        // Setup titles and X-axis labels
        $graph->xaxis->title->Set('Time');
        $graph->xaxis->SetTickLabels($timeStamps);
        $graph->xaxis->SetLabelAngle(90);
        $graph->xaxis->SetTextLabelInterval(2); 

        // Setup Y-axis title
        $graph->yaxis->title->Set('Amount');

        $weight = 1;
        foreach ($accounts as $accountId => $accountData) {
            $plot = new LinePlot($accountData);
            $plot->SetWeight($weight);

            $legend = $accountInfo[$accountId]['name'].' ('.$accountInfo[$accountId]['number'].')';
            $plot->SetLegend($legend);

            $graph->Add($plot);
            // Change the colour of the next account.
            $weight++;
        }

        // Display the graph
        $graph->Stroke();
    }
}

/* vim: set expandtab ts=4 sw=4: */
