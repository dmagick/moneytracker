<div class="middle">
    <h2>Edit this account</h2>
    ~flashmessage~
    <form method="post" action="~url::baseurl~/account/edit/~account_id~">
        <div class="account-field">
            Name:
        </div>
        <div class="account-value">
            <input type="text" name="account_name" value="~account_name~" id="account_name" />
        </div>

        <div class="account-field">
            Number:
        </div>
        <div class="account-value">
            <input type="text" name="account_number" value="~account_number~"/>
        </div>

        <div class="account-field">
            Balance:
        </div>
        <div class="account-value">
            <input type="text" name="account_balance" value="~account_balance~" id="account_balance" />
        </div>

        <div class="account-value">
            <br/>
            <input type="submit" value="Update Account" />
        </div>
    </form>
</div>
<script>
$("form").submit(function() {
    _a_name = $("#account_name");
    if (_a_name.val() == "") {
        alert("Enter an account name");
        _a_name.focus();
        return false;
    }

    _a_balance = $("#account_balance");
    if (_a_balance.val() == "") {
        alert("Enter an account balance");
        _a_balance.focus();
        return false;
    } else {
        if (isNaN(parseFloat(_a_balance.val()))) {
            alert("Enter a proper account balance.");
            _a_balance.select();
            _a_balance.focus();
            return false;
        }
    }
});
</script>

