                <div class="middle">
                    <div>
                        <h2>Login to manage your money</h2>
                        ~flashmessage~
                        <form method="post" action="~url::baseurl~/user/login">
                            <input type="hidden" name="token" value="~token~" />
                            <div class="account-field">
                                Username:
                            </div>
                            <div class="account-value">
                                <input type="text" name="username" value="" id="username" />
                            </div>
                            <div class="account-field">
                                Password:
                            </div>
                            <div class="account-value">
                                <input type="password" name="userpassword" value="" id="userpassword" />
                            </div>

                            <div class="account-value">
                                <br/>
                                <input type="submit" value="Login" />
                            </div>
                        </form>
                    </div>
                    <script>
                    $("form").submit(function() {
                        _user_name = $("#username");
                        if (_user_name.val() == "") {
                            alert("I'm sure you need to enter a username.");
                            _user_name.focus();
                            return false;
                        }

                        _user_pass = $("#userpassword");
                        if (_user_pass.val() == "") {
                            alert("I'm sure you need to enter a password.");
                            _user_pass.focus();
                            return false;
                        }
                    });
                    </script>
                </div><!-- end div middle//-->

