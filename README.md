# osTicket Email Fields Plugin
osTicket plugin to fetch fields from email headers on ticket creation.

## Installation
- Download master [zip](https://github.com/scand/osticket-email-fields/archive/master.zip) and extract into `/include/plugins/scand-email-fields`
- Then Install and enable as per normal osTicket Plugins

## Configuration
Provide list of pairs "header:field" as specified below, where header is name of email header and field is name of standard or custom field.
```
X-Email-Topic-Id:topicId
X-Email-Product-Name:product_name
```

In this case, if email will contains headers
```
X-Email-Topic-Id:10
X-Email-Product-Name:Scand osTicket Plugin
```
topicId will be equal to 10, and product_name will be equal to 'Scand osTicket Plugin'.

## Notes
Due to issue with passing variables by reference [#4287](https://github.com/osTicket/osTicket/issues/4287) standard fields can not be replaced by email values until but is not fixed.

To fix that you can manually change send() function in /include/class.signal.php file as below:
```
static function send($signal, $object, &$data=null) {
    if (!isset(self::$subscribers[$signal]))
        return;
    foreach (self::$subscribers[$signal] as $sub) {
        list($s, $callable, $check) = $sub;
        if ($s && !is_a($object, $s))
            continue;
        elseif ($check && !call_user_func_array($check, array($object, &$data)))
            continue;
        call_user_func_array($callable, array($object, &$data));
    }
}
```