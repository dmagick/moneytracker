<?php
/**
 * Account class file.
 *
 * @author Chris Smith <dmagick@gmail.com>
 * @version 1.0
 * @package money
 */

/**
 * Account class.
 * Handles fetching, listing, processing of new
 * and existing accounts.
 *
 * @package money
 */
class Account
{

    /**
     * Get account details from the database.
     *
     * @return array Returns an array of account information.
     *
     * @uses db::fetchAll
     * @uses db::getPrefix
     * @uses db::select
     */
    public static function getAccounts()
    {
        $sql   = "SELECT
                    account_id, account_name, account_number,
                    account_balance, username
                  FROM
                    ".db::getPrefix()."accounts a
                    INNER JOIN ".db::getPrefix()."users u 
                    ON (a.account_by=u.user_id)
                  ORDER BY
                    account_name DESC";
        $query = db::select($sql);
        $rows  = db::fetchAll($query);
        return $rows;
    }

    /**
     * Uses templates to show a list of accounts for viewing/editing.
     *
     * @return void
     *
     * @uses account::getAccounts
     * @uses template::display
     * @uses template::serveTemplate
     * @uses template::setKeyword
     */
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

    /**
     * Handles processing of a new account.
     *
     * If you are not posting data, it shows the account.new template.
     * If you are posting data, it checks to make sure certain values are set.
     *
     * If they aren't set, appropriate flash messages are saved.
     * If everything is ok the new account is created.
     *
     * The user is redirected back to the account list (in both cases).
     *
     * @return void Doesn't return anything, processes the templates.
     *
     * @uses db::execute
     * @uses db::getPrefix
     * @uses session::get
     * @uses session::save
     * @uses session::setFlashMessage
     * @uses template::display
     * @uses template::serveTemplate
     * @uses url::redirect
     */
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

        $accountInfo = array(
                        'account_balance' => $_POST['account_balance'],
                        'account_by'      => session::get('user'),
                        'account_name'    => $_POST['account_name'],
                        'account_number'  => $_POST['account_number'],
                       );
        $result      = self::saveAccount($accountInfo);

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

    /**
     * Save account info into the database.
     *
     * If there is an account id present in the array, then that id is updated.
     * If it's not present, a new account is created.
     *
     * @param array $accountInfo The account info to either create a new one or
     *                           update an existing one.
     *
     * @return boolean Returns true if it worked, otherwise false.
     */
    public static function saveAccount(array $accountInfo)
    {
        /**
         * account_id isn't a required field -
         * in case we're creating a new account.
         */
        $requiredFields = array(
                           'account_balance',
                           'account_by',
                           'account_name',
                           'account_number',
                          );

        foreach ($requiredFields as $requiredField) {
            if (isset($accountInfo[$requiredField]) === FALSE) {
                throw new Exception('Unable to save an account, some fields are missing.');
            }
        }

        $values = array(
                   ':account_balance' => $accountInfo['account_balance'],
                   ':account_by'      => $accountInfo['account_by'],
                   ':account_name'    => $accountInfo['account_name'],
                   ':account_number'  => $accountInfo['account_number'],
                  );
        if (isset($accountInfo['account_id']) === FALSE) {
            $sql = "INSERT INTO ".db::getPrefix()."accounts(account_name, account_number, account_balance, account_by) VALUES (:account_name, :account_number, :account_balance, :account_by)";
        } else {
            $sql                  = "UPDATE ".db::getPrefix()."accounts SET account_balance=:account_balance, account_name=:account_name, account_number=:account_number, account_by=:account_by WHERE account_id=:account_id";
            $values['account_id'] = $accountInfo['account_id'];
        }
        $result = db::execute($sql, $values);
        return $result;
    }

    /**
     * Handles processing of an existing account.
     *
     * If you are not posting data, it shows the account.edit template
     * with the existing values shown.
     * If you are posting data, it checks to make sure certain values are set.
     *
     * If they aren't set, appropriate flash messages are saved.
     * If everything is ok the account is updated.
     *
     * The user is redirected back to the account list (in both cases).
     *
     * @return void Doesn't return anything, processes the templates.
     *
     * @uses db::execute
     * @uses db::getPrefix
     * @uses session::get
     * @uses session::save
     * @uses session::setFlashMessage
     * @uses template::display
     * @uses template::serveTemplate
     * @uses template::setKeyword
     * @uses url::redirect
     */
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

        $accountInfo = array(
                        'account_balance' => $_POST['account_balance'],
                        'account_by'      => session::get('user'),
                        'account_id'      => $account_id,
                        'account_name'    => $_POST['account_name'],
                        'account_number'  => $_POST['account_number'],
                       );
        $result      = self::saveAccount($accountInfo);

        if ($result === TRUE) {
            session::setFlashMessage('Account updated successfully.', 'success');
            url::redirect('account/list');
            return;
        }
        session::setFlashMessage('Account not updated successfully.', 'error');
        url::redirect('account/edit/'.$account_id);
    }

    /**
     * Processes the account system.
     *
     * Works out what you are trying to do based on the action passed in,
     * then shows the appropriate sub-process.
     *
     * @param string $action The action being performed. Defaults to listing
     *                       of accounts.
     *
     * @return mixed     Returns the value from the sub-process being processed.
     * @throws exception Throws an exception if an action isn't known.
     *
     * @uses account::deleteAccount
     * @uses account::editAccount
     * @uses account::listAccounts
     * @uses account::newAccount
     * @uses template::display
     * @uses template::serveTemplate
     */
    public static function process($action='list')
    {

        if (empty($action) === TRUE) {
            $action = 'list';
        }

        template::serveTemplate('account.header');

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
