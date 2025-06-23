<?php

namespace MyApp;

use Exception;
use MyApp\Database\MySQL;

class Member
{

    public int|string   $id;
    public string       $full_name;
    public string|array $email;
    public string|array $phone;
    public string       $password;
    public string       $current_password;
    private string      $OTP;
    public string       $status = "";
    public int|string   $staff_member = "0";
    public string|int   $role_id = "0";
    public array|bool|object $orders;
    public array        $info;
    public array        $errors_list = [];
    public string       $success_m = '';
    public string       $error_m = '';
    protected string    $table_name = 'member';
    public string $table_id;
    protected MySQL $mySQL;

    public function __construct($id='')
    {
        /** @var MySQL $mySQL */
        $this->mySQL = MySQL::get_instance();
        $this->id = $id;
        $this->id !== '' && $this->info = $this->get();
    }

    /**
     * Get single member
     * @return bool|array
     */
    public function get(): bool|array
    {
        return $this->mySQL->table($this->table_name)
            ->where("id", $this->id)
            ->fetch();
    }

    /**
     * Get all members
     * @return bool|array
     */
    public function get_all(): bool|array
    {
        if(!(new Auth())->has("l_p_administrate_Member_"))
            return false;
        return $this->mySQL->fetchAll($this->table_name);
    }

    /**
     * Get All customer orders
     * @return object|bool|array
     */
    public function get_orders(): object|bool|array
    {
        return $this->orders = $this->mySQL->table('order_details')
            ->where('customer_id', $this->id)
            ->fetchAll();
    }

    /**
     * Get Order details
     * @param $order_id
     * @return object|bool|array
     */
    public function get_order_details($order_id): object|bool|array
    {
        $order = new Order();
        $order->details = $order->get_order_by_id($order_id);
        return $order->get_details();
    }

    /**
     * @param string $permission
     * @return bool
     */
    public function allowed(string $permission): bool
    {
        if(!(new Auth())->has($permission)){
            $this->errors_list["error_m"] = sprintf(l_you_dont_have_permission, constant($permission));
            return false;
        }
        return true;
    }

    /**
     * Check if authorized to manage member
     * @param string|int|null $company_id
     * @param string $permission
     * @return bool
     */
    public function authorized(string|int|null $company_id, string $permission): bool
    {
        global $sess;
        if(
            // IF YOU MODIFY ANOTHER MEMBER
            $sess->logged_id != $this->id
            && (
                !(
                    // IF YOU MODIFY NONE STAFF MEMBER
                    $this->mySQL->where("id", $this->id)
                        ->table($this->table_name)
                        ->getThis("staff_member") == "0"
                )
                // AS WELL AS YOU MUST HAVE THE PERMISSION
                && $this->allowed($permission)
            )
            // ADMINISTRATOR OVERRIDES ALL PRIVILEGES
            && !$this->allowed("l_p_administrate_Member_")
        ){
            $this->errors_list["error_m"] = l_this_member_is_not_managed_by_your_group;
            return false;
        }
        return true;
    }

    /**
     * Validate member information when create or edit
     *
     * @param bool $password
     * @param bool $email
     * @param bool $phone
     * @return bool
     */
    public function validate(
        bool $password  = true,
        bool $email     = true,
        bool $phone     = true
    ): bool
    {
        if (Validate::is_set($this->full_name))
            if (!Validate::is_set($this->full_name) || !Validate::max255($this->full_name))
                $this->errors_list[] = [l_invalid_full_name, "full_name"];
            else
                $this->errors_list[] = [l_full_name_cannot_be_empty, "full_name"];

        $password && $this->validate_password();
        $this->validate_email($email);
        $this->validate_phone($phone);

        return count($this->errors_list);
    }

    /**
     * Login action
     *
     * @param string $login
     * @param string $password
     * @param bool $stay
     * @return bool
     */
    function login(string $login, string $password, bool $stay): bool
    {
        global $sess;
        if (!empty($login) && !empty($password)) {
            if ($member_id = $this->member_by_login($login)) {

                // Get the member password to validate
                $pwd = $this->mySQL->where("id", $member_id)
                    ->table($this->table_name)
                    ->getThis("password");

                if (password_verify($password, $pwd)) {
                    // Check status
                    if ($this->membership_checks($member_id)){
                        // lets login
                        $sess->logged_in = true;
                        /**
                         * TODO fix security issues
                         */
                        if($stay)
                            setcookie('user_id', $member_id, time() + (30 * 24 * 60 * 60), '/');
                        else {
                            /*session_regenerate_id();
                            session_unset();*/
                            $_SESSION['user_id'] = $member_id;
                        }
                        $sess->logged_id = $member_id;
                        $this->mySQL->table('member')->where("id", $member_id)
                            ->update(["last_access_ip", "last_access_time"], [get_client_ip(), "NOW()"]);
                        // update cart status
                        $cart = new Cart();
                        if($cart->has_session_cart()) {
                            $cart->clear_unused_carts();
                            $this->mySQL->table('cart')
                                ->where('session_token', $_COOKIE['session-token'])
                                ->update('customer_id', $sess->logged_id);
                        }
                        return true;
                    }
                }
                else
                    $this->error_m = l_incorrect_password; // Password doesn't match
            }
            else
                $this->error_m = l_member_cannot_be_found; // User not found
        }
        else
            $this->error_m = l_login_or_password_cannot_be_empty; // Missing entries
        return false;
    }

    /**
     * Membership checks
     * @return bool:
     */
    private function membership_checks($id): bool
    {
        $db = MySQL::get_instance();
        $member = $db->table($this->table_name)->found('id', $id);
        $this->status = $member['status'];
        switch ($this->status) {
            case 0:
            case 1:
                return true;
            case 2:
                $this->error_m = l_membership_is_suspended;
                break;
        }
        return false;
    }

    /**
     * Get member by username or phone
     * @return int|bool (member ID if found or 0 if not found)
     */
    public function member_by_login(string $login): int|bool
    {
        // Determine the login method mail or phone
        $method = is_numeric($login) ? "phone" : "email";

        // Get the member id that belongs to the login method
        return $this->mySQL->where($method, $login)
            ->table($this->table_name)
            ->getThis("id");
    }

    /**
     * Registering a new member
     * @param array $post
     * @param bool $by_admin
     * @return bool|int
     */
    public function register(array $post, bool $by_admin = false): bool|int
    {
        $this->set_local_properties($post);

        // Default columns and values
        $columns = ["full_name", "token", 'token_time', "email", "phone", "last_access_ip", "last_access_time", "creator", "modifier"];
        $values = [$this->full_name, $this->OTP, time() + 3600, $this->email, $this->phone, get_client_ip(), "NOW()", 0, 0];

        // PASSWORD & STATUS AUTO BY ADMIN
        if($by_admin){
            $this->password = gen_strong_pwd();
            $this->status = "1";
        }

        // ALWAYS STORE HASHED PASSWORDS
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);

        if($this->table_name === "member") {
            $columns[] = "staff_member";
            $values[] = $this->staff_member;

            $columns[] = "role_id";
            $values[] = $this->role_id;
        }

        // STATUS ALWAYS WRITTEN
        $columns[] = "status";
        $values[] = $this->status == '' ? 0 : $this->status;

        // PASSWORD INSERTING AFTER HASHING
        $columns[] = "password";
        $values[] = $hashed_password;

        // VALIDATING EVERYTHING ELSE
        if (!$this->validate()) {

            // EMAIL IF REGISTERING HIM/HER SELF
            $subject = l_your_account_activation_link;
            $otp = $this->OTP;
            $link = "https://" . $_SERVER["HTTP_HOST"];
            $message = str_replace(
                ['{{OTP}}', '{{name}}', '{{link_to_us}}', '{{email}}'],
                [$otp, $this->full_name, $link, $this->email], l_activation_message
            );

            if($by_admin){
                // EMAIL IF BY ADMIN
                $subject = l_admin_created_an_account_for_you;
                $link = "https://" . $_SERVER["HTTP_HOST"] . "/login?password-recovery=true&email=" . $this->email . "&token=" . $this->OTP;
                $message = str_replace(
                    ["{{name}}", "{{link}}", "{{email}}", "{{password}}"],
                    [$this->full_name, $link, $this->email, $this->password],
                    l_account_created_message);
            }

            // TIME TO INSERT EVERYTHING
            if ($inserted_id = $this->mySQL->table($this->table_name)->insert($columns, $values)) {

                // SENDING EMAIL AFTER REGISTRATION
                send_mail($this->email, $subject, $message);

                // SUCCESSFULLY
                $this->login($this->email, $this->password, false);
                return $inserted_id;

            } else {
                // UNKNOWN ERROR
                $this->error_m = l_failed_to_insert_into_database;
                return false;
            }
        } else {
            // THERE IS ERRORS WERE COUNT
            return false;
        }
    }

    /**
     * Creating a new member
     * @param array $post
     * @return bool
     * @throws Exception
     */
    public function create(array $post): bool
    {
        return $this->register($post, true);
    }

    /**
     * Update an exiting member
     * @param array $post
     * @return bool
     */
    public function update(array $post): bool
    {
        global $sess;
        // WHICH ONE IS BEING MODIFIED
        $this->id = $post["id"] ?? $sess->logged_id;

        // SET THE LOCAL VARS
        $this->set_local_properties($post);
        $this->current_password = $post["current-password"] ?? "";

        if(!Validate::is_set($this->current_password)){
            $this->error_m = l_you_must_provide_your_current_password;
            return false;
        }

        // STORING THE MODIFIED MEMBER TO LOOK UP THE CHANGES AND MAKE SURE HE/SHE IS EXISTS
        if (!$member = $this->mySQL->table($this->table_name)->found("id", $this->id)) {
            $this->error_m = l_member_not_found;
            return false;
        }

        if(!password_verify($this->current_password, $member['password'])){
            $this->error_m = l_incorrect_password;
            return false;
        }

        // DEFAULT COLUMNS AND VALUES THAT GOING TO BE MODIFIED
        $columns = ["full_name", "modifier"];
        $values = [$this->full_name, $sess->logged_id];

        // LET'S SEE WHITHER THE PASSWORD IS GOING TO BE UPDATED
        if ($password = $this->password != "") {
            $columns[] = "password";
            $values[] = password_hash($this->password, PASSWORD_DEFAULT);
        }

        // LET'S SEE WHITHER THE EMAIL ADDRESS IS GOING TO BE UPDATED
        $email = $member["email"] != $this->email;

        // LET'S SEE WHITHER THE PHONE ADDRESS IS GOING TO BE UPDATED
        if ($phone = $member["phone"] != $this->phone) {
            $columns[] = "phone";
            $values[] = $this->phone;
        }

        if (!$this->validate($password, $email, $phone)) {
            // NOW TIME TO DO THE UPDATE
            if ($this->mySQL->table($this->table_name)
                ->where("id", $this->id)
                ->update($columns, $values)) {
                // TODO
                //($email) && $this->update_email($this->email);

                return true; // UPDATED SUCCESSFULLY
            } else {
                $this->error_m = l_you_did_not_updated_any_thing;
                return true; // UNKNOWN ERROR
            }
        } else {
            return false; // COUNTED SOME ERRORS
        }
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        global $sess;
        $this->id = $id;

        // member must be exits
        if(!$member = $this->get())
            $this->error_m = l_member_not_found;

        // Can't delete yourself
        if($sess->logged_id == $member["id"] ){
            $this->error_m = l_you_cant_delete_your_self;
            return false;
        }

        if(!$this->authorized($member["company_id"], "l_p_delete_Member_")){
            return false;
        }

        // Confirm he/she has no relations
        $tables = $this->mySQL->getTables();
        foreach ($tables as $table){
            if($columns = $this->mySQL->table($table)->showColumns()){
                foreach ($columns as $column){
                    if(
                        ($column == "creator" && $this->mySQL->table($table)->found("creator", $this->id)) ||
                        ($column == "modifier" && $this->mySQL->table($table)->found("modifier", $this->id))
                    ){
                        $this->error_m = l_member_is_in_use;
                        return false;
                    }
                }
            }
        }

        // make sure there is at least one super admin
        if (
            !$this->mySQL->table($this->table_name)->where("role_id", "1")
                ->and_where("id", $this->id, "<>")
                ->fetchAll()
        ) {
            $this->error_m = l_please_assign_another_account_as_admin_first;
            return false;
        }

        // Safe to delete
        if ($this->mySQL->table($this->table_name)->where("id", $this->id)->delete()) {
            return true;
        } else {
            $this->error_m = l_failed_to_delete_from_database;
            return false;
        }
    }

    /**
     * Update Activate OTP
     * @return int
     */
    public function update_activate_otp(): int
    {
        $this->OTP = $this->genOTP();
        return $this->mySQL->table($this->table_name)
            ->where('email', $this->info['email'])
            ->update(['token', 'token_time'], [$this->OTP, time() + 3600]);
    }

    /**
     *
     * @param array $post
     * @param bool $resend
     * @return string
     */
    public function verify(array $post, bool $resend = false): string
    {
        if ($resend) {
            // Update OTP
            $this->update_activate_otp();

            // Prepare resend & create new OPT
            $link = "https://" . $_SERVER["HTTP_HOST"];
            $message = str_replace(
                ["{{OTP}}", "{{name}}", "{{link_to_us}}", '{{email}}'],
                [$this->OTP, $this->info["full_name"], $link, $this->info['email']], l_resend_email_validate_message);

            $this->success_m = sprintf(l_email_verification_has_been_sent, $this->info['email']);

            // SENDING NEW OTP
            return send_mail($this->info['email'], l_re_generate_activation_link, $message);
        }

        if ($this->info["status"]) {
            $this->error_m = l_email_already_active;
            return false;
        }

        $token = json_decode($post['token']);


        if(time() - $this->info["token_time"] > 3600) {
            $this->error_m = l_this_token_is_expired;
            return false;
        }

        if ($this->mySQL->where("token", $token)
            ->and_where("email", $this->info["email"])
            ->table($this->table_name)
            ->update(["status", "token"], ["1", $this->genOTP()])) {
            $this->success_m = l_email_activated_successfully;
            return true;
        } else {
            $this->error_m = l_invalid_otp;
            return false;
        }
    }

    /**
     * @param $email
     * @return string
     */
    public function send_password_link($email): string
    {
        if(!Validate::is_set($email)) {
            $this->error_m = l_email_cannot_be_empty;
            return false;
        }

        if(!$member = $this->mySQL->table($this->table_name)->found("email", $email)) {
            $this->error_m = sprintf(l_email_not_found, $email);
            return false;
        }

        // generate OTP
        $password_token = sha1(uniq_id_real());
        // update token and its time
        $this->mySQL->table($this->table_name)
            ->where("id", $member["id"])
            ->update(["token", "token_time"], [$password_token, time() + 3600]);

        // Store password link
        $link = "https://" . $_SERVER["HTTP_HOST"] . "/new-password&email=" . $email . "&token=" . $password_token;

        // generate message
        $message = str_replace(
            ["{{email}}", "{{link}}"],
            [$member["email"], $link], l_password_recovery_message
        );

        // Send password link
        if (send_mail($email, l_your_password_recovery_link, $message)) {
            $this->success_m = sprintf(l_password_recovery_has_been_sent, $email);
            return true;
        }
        // sending error accord
        else
            $this->error_m = sprintf(l_password_recovery_could_not_been_sent, $email);
        return false;
    }

    /**
     * @param array $post
     * @return bool
     */
    public function reset_password(array $post): bool
    {
        /** @var MySQL $mysqlDB */
        $mysqlDB = MySQL::get_instance();

        if($member = $this->validate_recovery_link($post)){
            $this->password = $post["password"];

            // to detect count of errors and need to be Zero
            if(!$this->validate_password())
            {
                $password = password_hash($post["password"], PASSWORD_DEFAULT);
                $mysqlDB->table($this->table_name)
                    ->where("id", $member["id"])
                    ->update(["password", "token"], [$password, sha1(uniqid())]);
                $this->success_m = l_password_changed_successfully;
                return true;
            } else {
                $this->error_m = $this->errors_list[0][0];
                return false;
            }
        } else {
            $this->error_m = $this->errors_list[0][0];
            return false;
        }
    }

    /**
     * validate recovery link
     *
     * @param array $get
     * @return bool|array
     */
    public function validate_recovery_link(array $get): bool|array
    {
        /** @var MySQL $mysqlDB */
        $mysqlDB = MySQL::get_instance();

        if(
            $member = $mysqlDB->table($this->table_name)->where("email", $get["email"])
                ->and_where("token", $get["token"])
                ->fetch()
        ){
            if(time() - $member["token_time"] > 60 * 60) {
                $this->errors_list[] = [l_this_link_is_expired];
                return false;
            }

            return $member;
        } else {
            $this->errors_list[] = [l_invalid_reset_link];
            return false;
        }
    }

    /**
     * Update profile email
     * @param string $email
     * @return bool
     */
    public function update_email(string $email): bool
    {
        /** @var MySQL $mysqlDB */
        $mysqlDB = MySQL::get_instance();

        // we Already have current member info saved to
        // $this->info lets see if email still not active
        $replace_current =  ($this->info['status'] == '0');

        $this->email = $email;

        if ($this->validate_email()) {
            $this->error_m = $this->errors_list[0][0];
            return false;
        }

        // Update OTP
        $this->update_activate_otp();

        if ($mysqlDB->table($this->table_name)
            ->where('id', $this->info['id'])
            ->update($replace_current ? 'email' : 'temp_email', $this->email))
        {

            // Prepare resend & create new OPT
            $link = "https://" . $_SERVER["HTTP_HOST"];
            $message = str_replace(
                ["{{OTP}}", "{{name}}", "{{link_to_us}}", '{{new_email}}', '{{old_email}}'],
                [$this->OTP, $this->info["full_name"], $link, $this->email, $this->info['email']], l_update_email_validate_message);

            $this->success_m = sprintf(l_email_verification_has_been_sent, $this->info['email']);

            // SENDING NEW OTP
            return send_mail($this->email, l_your_account_activation_link, $message);

        }
        return false;
    }

    /**
     * Get post variables to set in register and update
     * @param array $post
     * @return void
     */
    private function set_local_properties(array $post): void
    {
        // THIS DATA ALWAYS PROVIDED
        $this->full_name = $post["full_name"] ?? "";
        $this->email = $post["email"] ?? "";
        $this->phone = $post["phone"] ?? "";
        $this->OTP = $this->genOTP();

        // ONLY SET BY ADMINISTRATION LEVEL
        $this->role_id = $post["role_id"] ?? "0";

        // IF USER REGISTERING HIM-SELF NEED TO SET PASSWORD
        $this->password = $post["password"] ?? "";
    }

    private function validate_email(bool $email_exists_check = true): bool
    {
        /** @var MySQL $mysqlDB */
        $mysqlDB = MySQL::get_instance();

        // Email is set
        if (Validate::is_set($this->email)) {
            // valid && not long
            if (Validate::email($this->email) && Validate::max255($this->email)) {
                // Email already exists
                if ($email_exists_check && $mysqlDB->table($this->table_name)->found("email", $this->email))
                    $this->errors_list[] = [sprintf(l_this_email_address_is_already_exists, $this->email), "email"];

            } else {
                $this->errors_list[] = [sprintf(l_this_email_address_is_invalid, $this->email), "email"];
            }
        } else {
            $this->errors_list[] = [l_email_cannot_be_empty, "email"];
        }
        return count($this->errors_list);
    }

    private function validate_phone(bool $phone): bool
    {
        /** @var MySQL $mysqlDB */
        $mysqlDB = MySQL::get_instance();

        if (Validate::is_set($this->phone)) {
            if (Validate::phone($this->phone)) {
                if ($phone && $mysqlDB->table($this->table_name)->found("phone", $this->phone)) {
                    $this->errors_list[] = [sprintf(l_this_phone_number_is_already_exists, $this->phone), "phone"];
                }
            } else {
                $this->errors_list[] = [sprintf(l_this_phone_number_is_invalid, $this->phone), "phone"];
            }
        } else {
            $this->errors_list[] = [l_phone_cannot_be_empty, "phone"];
        }
        return count($this->errors_list);
    }

    private function validate_password(): bool
    {
        if (Validate::is_set($this->password)) {
            if (!Validate::password($this->password)) {
                $this->errors_list[] = [l_invalid_password_criteria, "password"];
            }
        } else {
            $this->errors_list[] = [l_password_cannot_be_empty, "password"];
        }

        return count($this->errors_list);
    }

    private function validate_current_password()
    {

    }

    private function genOTP(): int
    {
        return rand(10000, 99999);
    }

}