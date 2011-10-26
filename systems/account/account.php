<?php
class Account
{

    public static function getAccounts()
    {
        $sql   = "SELECT account_id, account_name, account_number, account_balance, username FROM ".db::getPrefix()."accounts a INNER JOIN ".db::getPrefix()."users u ON (a.account_by=u.user_id) ORDER BY account_name DESC";
        $query = db::select($sql);
		$rows  = db::fetchAll($query);
        return $rows;
    }

    public static function listAccounts()
    {
        $rows = Account::getAccounts();
        if (empty($rows) === TRUE) {
            echo 'No accounts have been created.';
            return;
        }

        template::serveTemplate('account.list.header');
        foreach ($rows as $row) {
            foreach (array('account_id', 'account_name', 'account_number', 'account_balance', 'username') as $keyword) {
                $value = $row[$keyword];
                template::setKeyword('account.list.detail', $keyword, $value);
            }
            template::serveTemplate('account.list.detail');
            template::display();
        }
        template::serveTemplate('account.list.footer');
        template::display();
    }

    public static function newAccount()
    {
        if (empty($_POST) === TRUE) {
            template::serveTemplate('account.new');
            template::display();
            return;
        }

        if (is_numeric($_POST['account_balance']) === FALSE) {
            session::setFlashMessage('New account not created successfully. Please enter a proper account balance', 'error');
            session::save();
            url::redirect('account/list');
            return;
        }

        $sql    = "INSERT INTO ".db::getPrefix()."accounts(account_name, account_number, account_balance, account_by) VALUES (:account_name, :account_number, :account_balance, :account_by)";
        $values = array(
                   ':account_balance' => $_POST['account_balance'],
                   ':account_by'      => session::get('user'),
                   ':account_name'    => $_POST['account_name'],
                   ':account_number'  => $_POST['account_number'],
                  );
        $result = db::execute($sql, $values);
        if ($result === TRUE) {
            session::setFlashMessage('New account created successfully.', 'success');
            session::save();
            url::redirect('account/list');
            return;
        }
        session::setFlashMessage('New account not created successfully.', 'error');
        session::save();
        url::redirect('account/new');
    }

    public static function editAccount($account_id)
    {
        if (empty($_POST) === TRUE) {
            $sql    = "SELECT account_id, account_name, account_number, account_balance FROM ".db::getPrefix()."accounts WHERE account_id=:account_id";
            $values = array(
                       ':account_id' => $account_id,
                      );
            $query  = db::select($sql, $values);
            $row    = db::fetch($query);

            foreach (array('account_id', 'account_name', 'account_number', 'account_balance') as $keyword) {
                $value = $row[$keyword];
                template::setKeyword('account.edit', $keyword, $value);
            }

            template::serveTemplate('account.edit');
            template::display();
            return;
        }

        $sql    = "UPDATE ".db::getPrefix()."accounts SET account_balance=:account_balance, account_name=:account_name, account_number=:account_number, account_by=:account_by WHERE account_id=:account_id";
        $values = array(
                   ':account_balance' => $_POST['account_balance'],
                   ':account_by'      => session::get('user'),
                   ':account_id'      => $account_id,
                   ':account_name'    => $_POST['account_name'],
                   ':account_number'  => $_POST['account_number'],
                  );

        $result = db::execute($sql, $values);
        if ($result === TRUE) {
            session::setFlashMessage('Account updated successfully.', 'success');
            url::redirect('account/list');
            return;
        }
        session::setFlashMessage('Account not updated successfully.', 'error');
        url::redirect('account/edit/'.$account_id);
    }


	public static function process($action='list')
	{

        if (empty($action) === TRUE) {
            $action = 'list';
        }

        template::serveTemplate('account.header');
        template::display();

		if ($action === 'new') {
			return self::newAccount();
		}

		if ($action === 'list') {
			return self::listAccounts();
		}

		if (strpos($action, 'edit') === 0) {
			list($action, $id) = explode('/', $action);
			return self::editAccount($id);
		}

		if (strpos($action, 'delete') === 0) {
			return self::deleteAccount();
		}

		throw new Exception("Unknown action $action");
	}
}

/* vim: set expandtab ts=4 sw=4: */
