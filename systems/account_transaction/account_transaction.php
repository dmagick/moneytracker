<?php
class Account_Transaction
{
    public static function listTransactions()
    {
        $sql   = "SELECT a.account_id, a.account_name, a.account_number, l.transaction_amount, EXTRACT(EPOCH FROM l.transaction_date) AS transaction_date, l.transaction_description, l.account_balance_previous, l.account_balance_new FROM ".db::getPrefix()."accounts a INNER JOIN ".db::getPrefix()."account_transactions_log l ON (a.account_id=l.account_id) ORDER BY l.transaction_date DESC";
        $query = db::select($sql);
		$rows  = db::fetchAll($query);
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

    public static function newTransaction()
    {
        if (empty($_POST) === TRUE) {
            $accounts = Account::getAccounts();
            $select  = '<select name="account_id" id="account_id">';
            $select .= '<option value="-1">Which account?</option>';
            foreach ($accounts as $row => $account) {
                $select .= '<option value="'.$account['account_id'].'">'.$account['account_name'].'</option>';
            }
            $select .= '</select>';
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
        $sql    = "INSERT INTO ".db::getPrefix()."account_transactions(account_id, transaction_amount, transaction_date, transaction_description) VALUES (:account_id, :transaction_amount, :transaction_date, :transaction_description)";
        $values = array(
                   ':account_id'         => $_POST['account_id'],
                   ':transaction_amount' => $_POST['transaction_amount'],
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
