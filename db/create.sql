drop trigger money_account_transactions on money_account_transactions;
drop function money_account_transactions();
drop table money_account_transactions_log;
drop table money_account_transactions;
drop table money_accounts;

create table money_accounts
(
  account_id serial not null primary key,
  account_name text,
  account_number text,
  account_balance numeric(15,2)
);

create table money_account_transactions
(
  transaction_id serial not null primary key,
  transaction_amount numeric(15,2),
  transaction_date timestamp,
  account_id int not null references money_accounts(account_id),
  transaction_description text
);

CREATE FUNCTION money_account_transactions() RETURNS trigger AS $money_account_transactions$
  BEGIN
    IF (TG_OP = 'INSERT') THEN
      INSERT INTO money_account_transactions_log(transaction_id, transaction_amount, transaction_date, transaction_description, account_id, account_balance_previous, account_balance_new)
      SELECT
        t.transaction_id, t.transaction_amount, t.transaction_date, t.transaction_description, t.account_id, a.account_balance, a.account_balance + NEW.transaction_amount
      FROM
        money_account_transactions t INNER JOIN money_accounts a ON (t.account_id=a.account_id)
      WHERE
        t.transaction_id=NEW.transaction_id
      ;
      UPDATE money_accounts SET account_balance = account_balance + NEW.transaction_amount WHERE account_id = NEW.account_id;
      RETURN NEW;
    END IF;
  END;
$money_account_transactions$ language plpgsql;
CREATE TRIGGER money_account_transactions AFTER INSERT ON money_account_transactions FOR EACH ROW EXECUTE PROCEDURE money_account_transactions();

create table money_account_transactions_log
(
   log_id serial not null primary key,
   transaction_id int not null references money_account_transactions(transaction_id),
   transaction_amount numeric(15,2),
   transaction_date timestamp,
   transaction_description text,
   account_id int not null references money_accounts(account_id),
   account_balance_previous numeric(15,2),
   account_balance_new numeric(15,2)
);


