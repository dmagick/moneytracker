<?php
/**
 * Account_Transaction class file.
 *
 * @author Chris Smith <dmagick@gmail.com>
 * @version 1.0
 * @package money
 */

/**
 * Account_Transaction class.
 * Handles fetching, listing, processing of new
 * and existing transactions.
 *
 * @package money
 */
class Account_Transaction
{

    /**
     * Get transaction details from the database.
     *
     * Gets the account id, name, number, transaction amount,
     * transaction date, description, previous balance, and
     * who logged the transaction.
     * This is ordered by transaction date in descending order.
     *
     * @param integer $number The number of transactions to get.
     *                        Defaults to 50.
     *
     * @return array Returns an array of transaction information.
     *
     * @uses db::fetchAll
     * @uses db::getPrefix
     * @uses db::select
     */
    public static function getTransactions($number=50)
    {
        $sql   = "SELECT
                    a.account_id,
                    a.account_name,
                    a.account_number,
                    l.transaction_amount,
                    EXTRACT(EPOCH FROM l.transaction_date) AS transaction_date,
                    l.transaction_description,
                    l.account_balance_previous,
                    l.account_balance_new,
                    u.username AS transaction_by
                  FROM
                    ".db::getPrefix()."accounts a
                    INNER JOIN ".db::getPrefix()."account_transactions_log l ON (a.account_id=l.account_id)
                    INNER JOIN ".db::getPrefix()."users u ON (l.transaction_by=u.user_id)
                  ORDER BY
                    l.transaction_date DESC
                  LIMIT ".(int)$number;
        $query = db::select($sql);
        $rows  = db::fetchAll($query);
        return $rows;
    }

    /**
     * Uses templates to show a list of transactions for viewing/editing.
     *
     * Shows the
     * - account id, name, number, previous/new balances,
     * - transaction amount, date, description,
     * - who did the transaction
     * for the last 50 transactions.
     *
     * @return void
     *
     * @uses db::fetchAll
     * @uses db::getPrefix
     * @uses db::select
     * @uses template::display
     * @uses template::serveTemplate
     * @uses template::setKeyword
     */
    public static function listTransactions()
    {
        $rows = self::getTransactions();

        if (empty($rows) === TRUE) {
            echo 'No transactions have been created.';
            return;
        }

        template::serveTemplate('account_transaction.list.header');
        $fields = array(
                   'account_id',
                   'account_balance_previous',
                   'account_balance_new',
                   'account_name',
                   'account_number',
                   'transaction_amount',
                   'transaction_by',
                   'transaction_description',
                  );
        foreach ($rows as $row) {
            $keyword = 'transaction_date';
            $value   = date('D jS M, Y', $row[$keyword]);
            template::setKeyword('account_transaction.list.detail', $keyword, $value);

            foreach ($fields as $keyword) {
                $value = $row[$keyword];
                template::setKeyword('account_transaction.list.detail', $keyword, $value);
            }
            template::serveTemplate('account_transaction.list.detail');
            template::display();
        }
        template::serveTemplate('account_transaction.list.footer');
        template::display();
    }

    /**
     * Handles processing of a new transaction.
     *
     * If you are not posting data, it shows the account_transaction.new
     * template, with a list of accounts shown to choose from.
     * If there is only one account in the system, that account is
     * automatically chosen.
     *
     * If you are posting data, it checks to make sure certain values are set.
     *
     * If they aren't set, appropriate flash messages are saved.
     * If everything is ok the new account is created.
     *
     * The user is redirected back to the account list (in both cases).
     *
     * @return void Doesn't return anything, processes the templates.
     *
     * @uses db::beginTransaction
     * @uses db::commitTransaction
     * @uses db::execute
     * @uses db::getPrefix
     * @uses db::rollbackTransaction
     * @uses session::get
     * @uses session::setFlashMessage
     * @uses template::display
     * @uses template::serveTemplate
     * @uses template::setKeyword
     * @uses url::redirect
     */
    public static function newTransaction()
    {
        if (empty($_POST) === TRUE) {
            $accounts = Account::getAccounts();
            $select  = '<select name="account_id" id="account_id">';

            $selected = '';
            if (sizeof($accounts) > 1) {
                $selected = ' SELECTED';
                $select .= '<option value="-1"'.$selected.'>Which account?</option>';
            }

            $selected = '';
            if (sizeof($accounts) == 1) {
                $selected = ' SELECTED';
            }
            foreach ($accounts as $row => $account) {
                $select .= '<option value="'.$account['account_id'].'"'.$selected.'>'.$account['account_name'].'</option>';
            }
            $select .= '</select>';

            $defaultDate = date('Y-m-d H:i');
            template::setKeyword('account_transaction.new', 'transaction_date', $defaultDate);
            template::setKeyword('account_transaction.new', 'account_list', $select);
            template::serveTemplate('account_transaction.new');
            template::display();
            return;
        }

        $errors = FALSE;

        if ($_POST['account_id'] < 0) {
            session::setFlashMessage('New transaction not created successfully. Please choose an account.', 'error');
            $errors = TRUE;
        }

        if (is_numeric($_POST['transaction_amount']) === FALSE) {
            session::setFlashMessage('New transaction not created successfully. Please enter a proper amount', 'error');
            $errors = TRUE;
        }

        if (empty($_POST['transaction_description']) === TRUE) {
            session::setFlashMessage('New transaction not created successfully. Please enter a proper description', 'error');
            $errors = TRUE;
        }

        if ($errors === TRUE) {
            url::redirect('account_transaction/list');
            return;
        }

        // If it's a withdrawl/payment, make sure it's a negative amount.
        if ($_POST['transaction_type'] === 'withdrawl') {
            if ((float)$_POST['transaction_amount'] > 0) {
                $_POST['transaction_amount'] = 0 - $_POST['transaction_amount'];
            }
        }

        $transaction_date = 'NOW()';
        if (empty($_POST['transaction_date']) === FALSE) {
            $transaction_date = $_POST['transaction_date'];
        }

        db::beginTransaction();
        /**
         * Updating the main account balance and saving the transaction log
         * is done in a trigger in the database. It's safer that way, and it
         * already has all the info it needs to do it. Here we'd have to get the new
         * transaction id, save the log, update the account etc. The db can handle
         * doing all of that for us.
         */
        $sql    = "INSERT INTO ".db::getPrefix()."account_transactions(account_id, transaction_amount, transaction_date, transaction_description, transaction_by) VALUES (:account_id, :transaction_amount, :transaction_date, :transaction_description, :transaction_by)";
        $values = array(
                   ':account_id'         => $_POST['account_id'],
                   ':transaction_amount' => $_POST['transaction_amount'],
                   ':transaction_by'     => session::get('user'),
                   ':transaction_date'   => $transaction_date,
                   ':transaction_description' => $_POST['transaction_description'],
                  );

        $result = db::execute($sql, $values);
        if ($result === TRUE) {
            $transaction_result = db::commitTransaction();
            if ($transaction_result === TRUE) {
                session::setFlashMessage('New transaction created successfully.', 'success');
            } else {
                db::rollbackTransaction();
                session::setFlashMessage('New transaction not created successfully. Something went wrong updating the account.', 'error');
            }
            url::redirect('account_transaction/list');
            return;
        }

        db::rollbackTransaction();
        session::setFlashMessage('New transaction not created successfully.', 'error');
        url::redirect('account_transaction/new');
    }

    /**
     * Processes the account-transaction system.
     *
     * Works out what you are trying to do based on the action passed in,
     * then shows the appropriate sub-process.
     *
     * @param string $action The action being performed. Defaults to listing
     *                       of transactions.
     *
     * @return mixed     Returns the value from the sub-process being processed.
     * @throws exception Throws an exception if an action isn't known.
     *
     * @uses account_transaction::newTransaction
     * @uses account_transaction::listTransactions
     * @uses template::display
     * @uses template::serveTemplate
     */
    public static function process($action='list')
    {

        if (empty($action) === TRUE) {
            $action = 'list';
        }

        template::serveTemplate('account_transaction.header');
        template::display();

        if ($action === 'new') {
            return self::newTransaction();
        }

        if ($action === 'list') {
            return self::listTransactions();
        }

        throw new Exception("Unknown action $action");
    }
}

/* vim: set expandtab ts=4 sw=4: */
