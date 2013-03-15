<?php
interface EmailInterface {
    public function send($email_address, $email_content);
}