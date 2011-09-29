<div>
	<h2>Create a new transaction</h2>
	~flashmessage~
	<form method="post" action="~url::baseurl~/account_transaction/new">
		<div class="account-field">
			Account:
		</div>
		<div class="account-value">
			~account_list~
		</div>

		<div class="account-field">
			Transaction Amount:
		</div>
		<div class="account-value">
			<input type="text" name="transaction_amount" id="transaction_amount" />&nbsp;& type:
			<select name="transaction_type">
				<option value="deposit">Deposit</option>
				<option value="withdrawl" SELECTED>Withdrawl/Payment</option>
			</select>
		</div>

		<div class="account-field">
			Transaction Date (leave blank for today):<br/>
			Format is yyyy-mm-dd hh:mm
		</div>
		<div class="account-value">
			<input type="text" name="transaction_date" />
		</div>

		<div class="account-field">
			Transaction Description:
		</div>
		<div class="account-value">
			<input type="text" name="transaction_description" id="transaction_description" />
		</div>

		<div class="account-value">
			<br/>
			<input type="submit" value="Create Transaction" />
		</div>
	</form>
</div>
<script>
$("form").submit(function() {
	_t_account = $("#account_id");
	if (_t_account.val() < 0) {
		alert("Choose an account");
		_t_account.focus();
		return false;
	}

	_t_amount = $("#transaction_amount");
	if (_t_amount.val() == "") {
		alert("Enter a transaction amount");
		_t_amount.focus();
		return false;
	} else {
		if (isNaN(parseFloat(_t_amount.val()))) {
			alert("Enter a proper amount.");
			_t_amount.select();
			_t_amount.focus();
			return false;
		}
	}

	_t_description = $("#transaction_description");
	if (_t_description.val() == "") {
		alert("Enter a description of the transaction");
		_t_description.focus();
		return false;
	}

});
</script>

