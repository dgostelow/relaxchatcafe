<?php

/**
 * AB_Notifications version for PHP CLI (no include/require conflicts etc)
 */

/**
 * Class DB
 *   PDO Wrapper
 * @see http://www.designosis.com/PDO_class/
 */
class DB extends PDO {
    /**
     * @var string
     */
    private $error = '';

    /**
     * @var int
     */
    public  $querycount = 0;

    /**
     * @param $dsn
     * @param string $user
     * @param string $password
     * @param $charset
     */
    public function __construct( $dsn, $user = '', $password = '', $charset ) {
        $options = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset}",
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => true
        );
        try {
            parent::__construct( $dsn, $user, $password, $options );
        } catch ( PDOException $e ) {
            $this->error = $e->getMessage();
        }
    } // __construct

    /**
     * @param $query
     * @param bool $bind
     * @param bool $handler
     * @return array|bool|int|PDOStatement
     */
    public function run( $query, $bind = false, $handler = false ) {
        $this->querycount++;
        try {
            if ( $bind !== false ) {
                $bind = (array) $bind;
                $dbh = $this->prepare( trim( $query ) );
                $dbh->execute( $bind );
            } else {
                $dbh = $this->query( trim( $query ) ); // because query is 3x faster than prepare+execute
            }
            if ( preg_match( '/^(select|describe|pragma)/i', $query ) ) {
                // if $query begins with select|describe|pragma, either return handler or fetch
                return ( $handler ) ? $dbh : $dbh->fetchAll();
            } else if ( preg_match( '/^(delete|insert|update)/i', $query ) ) {
                // if $query begins with delete|insert|update, return count
                return $dbh->rowCount();
            } else {
                return true;
            }
        } catch ( PDOException $e ) {
            $this->error = $e->getMessage();
            return false;
        }
    } // run

    /**
     * @param $pairs
     * @param $glue
     * @return string
     */
    private function prepBind( $pairs, $glue ) {
        $parts = array();
        foreach ( $pairs as $k => $v ) {
            $parts[] = "`$k` = ?";
        }

        return implode( $glue, $parts );
    } // prepBind

    /**
     * @param $table
     * @param $data
     * @param $where
     * @param bool $limit
     * @return array|bool|int|PDOStatement
     */
    public function update( $table, $data, $where, $limit = false ) {
        if ( is_array( $data ) && is_array( $where ) ) {

            $dataStr  = $this->prepBind( $data, ', ' );
            $whereStr = $this->prepBind( $where, ' AND ' );
            $bind = array_merge( $data, $where );
            $bind = array_values( $bind );

            $sql = "UPDATE `$table` SET $dataStr WHERE $whereStr";
            if ( $limit && is_int( $limit ) ) {
                $sql .= ' LIMIT '. $limit;
            }

            return $this->run($sql, $bind);
        }

        return false;
    } // update

    /**
     * @param $table
     * @param $data
     * @return array|bool|int|PDOStatement
     */
    public function insert( $table, $data ) {
        if ( is_array( $data ) ) {

            $dataStr = $this->prepBind( $data, ', ' );
            $bind = array_values( $data );

            $sql = "INSERT `$table` SET  $dataStr";

            return $this->run( $sql, $bind );
        }

        return false;
    } // insert

    /**
     * @param $table
     * @param $where
     * @param bool $limit
     * @return array|bool|int|PDOStatement
     */
    public function delete( $table, $where, $limit = false ) {
        if ( is_array( $where ) ) {

            $whereStr = $this->prepBind( $where, ' AND ' );
            $bind = array_values( $where );

            $sql = "DELETE FROM `$table` WHERE $whereStr";
            if ( $limit && is_int( $limit ) ) {
                $sql .= ' LIMIT '. $limit;
            }

            return $this->run( $sql, $bind );
        }

        return false;
    } // delete

} // DB

/**
 * Class DB_Stats
 *  get Database Stats from wp-config
 */
class DB_Stats {
    /**
     * @var
     */
    private $db_name;

    /**
     * @var
     */
    private $db_user;

    /**
     * @var
     */
    private $db_password;

    /**
     * @var
     */
    private $db_host;

    /**
     * @var
     */
    private $db_charset;

    /**
     * @var
     */
    private $db_wp_prefix;

    /**
     * get data for connecting to DB from wp-config
     */
    public function __construct() {
        $wp_conf_content = file_get_contents( dirname(__FILE__).'/../../../../../wp-config.php' );

        preg_match('/define.*DB_NAME.*\'(.*)\'/', $wp_conf_content, $m); // $db_name
        $this->db_name     = $m[ 1 ];

        preg_match('/define.*DB_USER.*\'(.*)\'/', $wp_conf_content, $m); // $db_user
        $this->db_user     = $m[ 1 ];

        preg_match('/define.*DB_PASSWORD.*\'(.*)\'/', $wp_conf_content, $m); // $db_password
        $this->db_password = $m[ 1 ];

        preg_match('/define.*DB_HOST.*\'(.*)\'/', $wp_conf_content, $m); // $db_host
        $this->db_host     = $m[ 1 ];

        preg_match('/define.*DB_CHARSET.*\'(.*)\'/', $wp_conf_content, $m); // $db_charset
        $this->db_charset  = $m[ 1 ];

        preg_match('/table_prefix.*/', $wp_conf_content, $m); // $db_wp_prefix
        preg_match('/\'(.*)\'/', $m[ 0 ], $m);
        $this->db_wp_prefix = str_replace("'", '', $m[ 0 ]);
    } // __construct

    /**
     * @return mixed
     */
    public function getDbName() {
        return $this->db_name;
    } // getDbName

    /**
     * @return mixed
     */
    public function getDbHost() {
        return $this->db_host;
    } // getDbHost

    /**
     * @return mixed
     */
    public function getDbUser() {
        return $this->db_user;
    } // getDbUser

    /**
     * @return mixed
     */
    public function getDbPassword() {
        return $this->db_password;
    } // getDbPassword

    /**
     * @return mixed
     */
    public function getDbCharSet() {
        return $this->db_charset;
    } // getDbCharSet

    /**
     * @return mixed
     */
    public function getDbWpPrefix() {
        return $this->db_wp_prefix;
    }

} // DB_Stats

/**
 * Class Notifications
 */
class Notifications {
    /**
     * @var array
     */
    private static $notifications_types = array(
        'event_next_day'   => 'SELECT * FROM ab_notifications WHERE slug = "event_next_day" AND active = 1',
        'evening_after'    => 'SELECT * FROM ab_notifications WHERE slug = "evening_after" AND active = 1',
        'evening_next_day' => 'SELECT * FROM ab_notifications WHERE slug = "evening_next_day" AND active = 1'
    );

    /**
     * @var array
     */
    private static $appointments_types = array(
        'event_next_day'           =>
            'SELECT a.*, c.*, s.*, st.full_name AS staff_name, st.email AS staff_email, ca.customer_id as customer_id, ss.price AS sprice
            FROM ab_customer_appointment ca
            LEFT JOIN ab_appointment a ON a.id = ca.appointment_id
            LEFT JOIN ab_customer c ON c.id = ca.customer_id
            LEFT JOIN ab_service s ON s.id = a.service_id
            LEFT JOIN ab_staff st ON st.id = a.staff_id
            LEFT JOIN ab_staff_service ss ON ss.staff_id = a.staff_id AND ss.service_id = a.service_id
            WHERE DATE(DATE_ADD(NOW(), INTERVAL 1 DAY)) = DATE(a.start_date)
            AND NOT EXISTS (SELECT id FROM ab_email_notification aen WHERE DATE(aen.created) = DATE(NOW()) AND aen.type = "agenda_next_day" AND aen.staff_id = a.staff_id)',
        'evening_after'            =>
            'SELECT a.*, c.*, s.*, cat.name AS category_name, ca.customer_id as customer_id, ss.price AS sprice
            FROM ab_customer_appointment ca
            LEFT JOIN ab_appointment a ON a.id = ca.appointment_id
            LEFT JOIN ab_customer c ON c.id = ca.customer_id
            LEFT JOIN ab_service s ON s.id = a.service_id
            LEFT JOIN ab_category cat ON cat.id = s.category_id
            LEFT JOIN ab_staff_service ss ON ss.staff_id = a.staff_id AND ss.service_id = a.service_id
            WHERE DATE(NOW()) = DATE(a.start_date)
            AND NOT EXISTS (SELECT id FROM ab_email_notification aen WHERE DATE(aen.created) = DATE(NOW()) AND aen.type = "reminder_evening_after" AND aen.customer_id = ca.customer_id)',
        'evening_next_day'     =>
            'SELECT a.*, c.*, s.*, cat.name AS category_name, ca.customer_id as customer_id, ss.price AS sprice
            FROM ab_customer_appointment ca
            LEFT JOIN ab_appointment a ON a.id = ca.appointment_id
            LEFT JOIN ab_customer c ON c.id = ca.customer_id
            LEFT JOIN ab_service s ON s.id = a.service_id
            LEFT JOIN ab_category cat ON cat.id = s.category_id
            LEFT JOIN ab_staff_service ss ON ss.staff_id = a.staff_id AND ss.service_id = a.service_id
            WHERE DATE(DATE_ADD(NOW(), INTERVAL 1 DAY)) = DATE(start_date)
            AND NOT EXISTS (SELECT id FROM ab_email_notification aen WHERE DATE(aen.created) = DATE(NOW()) AND aen.type = "reminder_evening_next_day" AND aen.customer_id = ca.customer_id)'
    );

    /**
     * @var
     */
    private $db;

    /**
     * @var
     */
    private $stats;

    /**
     * Newline preservation help function for wpautop
     *
     * @since 3.1.0
     * @access private
     * @param array $matches preg_replace_callback matches array
     * @return string
     */
    public function _autop_newline_preservation_helper( $matches ) {
        return str_replace("\n", "<WPPreserveNewline />", $matches[0]);
    }

    /**
     * Replaces double line-breaks with paragraph elements.
     *
     * A group of regex replaces used to identify text formatted with newlines and
     * replace double line-breaks with HTML paragraph tags. The remaining
     * line-breaks after conversion become <<br />> tags, unless $br is set to '0'
     * or 'false'.
     *
     * @since 0.71
     *
     * @param string $pee The text which has to be formatted.
     * @param bool $br Optional. If set, this will convert all remaining line-breaks after paragraphing. Default true.
     * @return string Text which has been converted into correct paragraph tags.
     */
    public function wpautop($pee, $br = true) {
        $pre_tags = array();

        if ( trim($pee) === '' )
            return '';

        $pee = $pee . "\n"; // just to make things a little easier, pad the end

        if ( strpos($pee, '<pre') !== false ) {
            $pee_parts = explode( '</pre>', $pee );
            $last_pee = array_pop($pee_parts);
            $pee = '';
            $i = 0;

            foreach ( $pee_parts as $pee_part ) {
                $start = strpos($pee_part, '<pre');

                // Malformed html?
                if ( $start === false ) {
                    $pee .= $pee_part;
                    continue;
                }

                $name = "<pre wp-pre-tag-$i></pre>";
                $pre_tags[$name] = substr( $pee_part, $start ) . '</pre>';

                $pee .= substr( $pee_part, 0, $start ) . $name;
                $i++;
            }

            $pee .= $last_pee;
        }

        $pee = preg_replace('|<br />\s*<br />|', "\n\n", $pee);
        // Space things out a little
        $allblocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|option|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|noscript|samp|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';
        $pee = preg_replace('!(<' . $allblocks . '[^>]*>)!', "\n$1", $pee);
        $pee = preg_replace('!(</' . $allblocks . '>)!', "$1\n\n", $pee);
        $pee = str_replace(array("\r\n", "\r"), "\n", $pee); // cross-platform newlines
        if ( strpos($pee, '<object') !== false ) {
            $pee = preg_replace('|\s*<param([^>]*)>\s*|', "<param$1>", $pee); // no pee inside object/embed
            $pee = preg_replace('|\s*</embed>\s*|', '</embed>', $pee);
        }
        $pee = preg_replace("/\n\n+/", "\n\n", $pee); // take care of duplicates
        // make paragraphs, including one at the end
        $pees = preg_split('/\n\s*\n/', $pee, -1, PREG_SPLIT_NO_EMPTY);
        $pee = '';
        foreach ( $pees as $tinkle )
            $pee .= '<p>' . trim($tinkle, "\n") . "</p>\n";
        $pee = preg_replace('|<p>\s*</p>|', '', $pee); // under certain strange conditions it could create a P of entirely whitespace
        $pee = preg_replace('!<p>([^<]+)</(div|address|form)>!', "<p>$1</p></$2>", $pee);
        $pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee); // don't pee all over a tag
        $pee = preg_replace("|<p>(<li.+?)</p>|", "$1", $pee); // problem with nested lists
        $pee = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $pee);
        $pee = str_replace('</blockquote></p>', '</p></blockquote>', $pee);
        $pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)!', "$1", $pee);
        $pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee);
        if ( $br ) {
            $pee = preg_replace_callback('/<(script|style).*?<\/\\1>/s', array($this, '_autop_newline_preservation_helper') , $pee);
            $pee = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $pee); // optionally make line breaks
            $pee = str_replace('<WPPreserveNewline />', "\n", $pee);
        }
        $pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*<br />!', "$1", $pee);
        $pee = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $pee);
        $pee = preg_replace( "|\n</p>$|", '</p>', $pee );

        if ( !empty($pre_tags) )
            $pee = str_replace(array_keys($pre_tags), array_values($pre_tags), $pee);

        return $pee;
    }

    /**
     * @param array $notifications
     * @param $type
     */
    public function processNotifications( array $notifications, $type ) {
        $date = new DateTime();
        switch ( $type ) {
            case 'event_next_day':
                if ($date->format( 'H' ) >= 18) {
                    $rows = $this->db->query( self::$appointments_types[ 'event_next_day' ] )
                        ->fetchAll(PDO::FETCH_OBJ);
                    if ( count( $rows ) ) {
                        $staff_schedules = array();
                        $staff_emails = array();
                        $tomorrow_date = '';
                        foreach ( $rows as $row ) {
                            $staff_schedules[$row->staff_id][] = $row;
                            $staff_emails[$row->staff_id] = $row->staff_email;
                            $tomorrow_date = $this->getFormattedDateTime( $row->start_date, 'date' );
                        }

                        foreach ( $staff_schedules as $staff_id => $collection ) {
                            $schedule = '<table>';
                            foreach ( $collection as $object ) {
                                $startDate = new DateTime($object->start_date);
                                $endDate = new DateTime($object->end_date);
                                $schedule .= '<tr>';
                                $schedule .= sprintf( '<td>%s<td>',
                                    ($startDate->format( 'H:i' ) . '-' . $endDate->format( 'H:i' ) ) );
                                $schedule .= sprintf( '<td>%s<td>', $object->title );
                                $schedule .= sprintf( '<td>%s<td>', $object->name );
                                $schedule .= '</tr>';
                            }
                            $schedule .= '</table>';

                            // replace shortcodes
                            $currentNotification = current( $notifications );
                            $message = $this->replace(
                                array(),
                                $currentNotification[ 'message' ],
                                $schedule
                            );

                            // add [[TOMORROW_DATE]] to email's subject
                            $subject = $currentNotification[ 'subject' ];

                            if ( preg_match( '/\[\[.*?\]\]/', $subject ) ) {
                                $subject = preg_replace( '/\[\[.*?\]\]/', $tomorrow_date, $subject );
                            }

                            // send mail & create emailNotification
                            if ( $this->send_mail( $staff_emails[$staff_id], $subject, $this->wpautop( $message ) ) ) {
                                foreach ( $collection as $object ) {
                                    $this->processEmailNotifications(
                                        $object->customer_id,
                                        $object->staff_id,
                                        'agenda_next_day',
                                        $date->format( 'Y-m-d H:i:s' )
                                    );
                                }
                            }
                        }
                    }
                }
                break;
            case 'evening_after':
                if ($date->format( 'H' ) >= 21) {
                    $rows = $this->db->query( self::$appointments_types[ 'evening_after' ] )
                        ->fetchAll(PDO::FETCH_OBJ);
                    if ( count( $rows ) ) {
                        foreach ( $rows as $row ) {
                            // replace shortcodes
                            $currentNotification = current( $notifications );
                            $message = $this->replace( array(
                                    'client_name'       => $row->name,
                                    'appointment_time'  => $this->getFormattedDateTime( $row->start_date, 'time' ),
                                    'appointment_date'  => $this->getFormattedDateTime( $row->start_date, 'date' ),
                                    'service_name'      => $row->title,
                                    'service_price'     => $this->formatPrice( $row->sprice ),
                                    'category_name'     => $row->category_name
                                ),
                                $currentNotification[ 'message' ]
                            );

                            // add [[COMPANY_NAME]] to email's subject
                            $subject = $currentNotification[ 'subject' ];
                            $company_name = $this->get_option( 'ab_settings_company_name' ) ?
                                $this->get_option( 'ab_settings_company_name' ) : '';

                            if ( preg_match( '/\[\[.*?\]\]/', $subject ) ) {
                                $subject = preg_replace( '/\[\[.*?\]\]/', $company_name, $subject );
                            }

                            // send mail & create emailNotification
                            if ( $this->send_mail( $row->email, $subject, $this->wpautop( $message ) )
                            ) {
                                $this->processEmailNotifications(
                                    $row->customer_id ? $row->customer_id : 0,
                                    $row->staff_id    ? $row->staff_id    : 0,
                                    'reminder_evening_after',
                                    $date->format( 'Y-m-d H:i:s' )
                                );
                            }
                        }
                    }
                }
                break;
            case 'evening_next_day':
                if ($date->format( 'H' ) >= 18) {
                    $rows = $this->db->query( self::$appointments_types[ 'evening_next_day' ] )
                        ->fetchAll(PDO::FETCH_OBJ);
                    if ( count( $rows ) ) {
                        foreach ( $rows as $row ) {
                            // replace shortcodes
                            $currentNotification = current( $notifications );
                            $message = $this->replace( array(
                                    'client_name'       => $row->name,
                                    'appointment_time'  => $this->getFormattedDateTime( $row->start_date, 'time' ),
                                    'appointment_date'  => $this->getFormattedDateTime( $row->start_date, 'date' ),
                                    'service_name'      => $row->title,
                                    'service_price'     => $this->formatPrice( $row->sprice ),
                                    'category_name'     => $row->category_name
                                ),
                                $currentNotification[ 'message' ]
                            );

                            // add [[COMPANY_NAME]] to email's subject
                            $subject = $currentNotification[ 'subject' ];
                            $company_name = $this->get_option( 'ab_settings_company_name' ) ?
                                $this->get_option( 'ab_settings_company_name' ) : '';

                            if ( preg_match( '/\[\[.*?\]\]/', $subject ) ) {
                                $subject = preg_replace( '/\[\[.*?\]\]/', $company_name, $subject );
                            }

                            // send mail & create emailNotification
                            if ( $this->send_mail( $row->email, $subject, $this->wpautop( $message ) ) ) {
                                $this->processEmailNotifications(
                                    $row->customer_id ? $row->customer_id : 0,
                                    $row->staff_id    ? $row->staff_id    : 0,
                                    'reminder_evening_next_day',
                                    $date->format( 'Y-m-d H:i:s' )
                                );
                            }
                        }
                    }
                }
                break;
        }
    } // processNotifications

    /**
     * @param int $customer_id
     * @param int $staff_id
     * @param string $type
     * @param string $date
     * @return array|bool|int|PDOStatement
     */
    public function processEmailNotifications( $customer_id, $staff_id, $type, $date ) {
        $table = 'ab_email_notification';
        $data  = array(
            'customer_id' => $customer_id,
            'staff_id'    => $staff_id,
            'type'        => $type,
            'created'     => $date
        );

        return $this->db->insert( $table, $data );
    } // processEmailNotifications

    /**
     * Emulates wp get_option()
     *
     * @param $option
     * @return mixed|null
     */
    public function get_option( $option ) {
        $option_value = null;
        $options_table = $this->stats->getDbWpPrefix() . 'options';

        $stmt = current( $this->db->query(
            'SELECT option_value FROM `'.$options_table.'` WHERE option_name = "'.$option.'"'
        )->fetchAll( PDO::FETCH_OBJ ) );

        if ( count( get_object_vars( $stmt ) ) ) {
            $option_value = $stmt->option_value;
        }

        return $option_value;
    } // get_option

    /**
     * Format price based on currency settings (Settings -> Payments).
     *
     * @param  string $price
     * @return string
     */
    public function formatPrice( $price ) {
        $result = '';
        $price  = number_format( $price, 2 );
        switch ( $this->get_option( 'ab_paypal_currency' ) ) {
            case 'AUD' :
                $result = 'A$' . $price;
                break;
            case 'BRL' :
                $result = 'R$ ' . $price;
                break;
            case 'CAD' :
                $result = 'C$' . $price;
                break;
            case 'RMB' :
                $result = $price . ' ¥';
                break;
            case 'CZK' :
                $result = $price . ' Kč';
                break;
            case 'DKK' :
                $result = $price . ' kr';
                break;
            case 'EUR' :
                $result = '€' . $price;
                break;
            case 'HKD' :
                $result = $price . ' $';
                break;
            case 'HUF' :
                $result = $price . ' Ft';
                break;
            case 'IDR' :
                $result = $price . ' Rp';
                break;
            case 'INR' :
                $result = $price . ' ₹';
                break;
            case 'ILS' :
                $result = $price . ' ₪';
                break;
            case 'JPY' :
                $result = '¥' . $price;
                break;
            case 'KRW' :
                $result = $price . ' ₩';
                break;
            case 'MYR' :
                $result = $price . ' RM';
                break;
            case 'MXN' :
                $result = $price . ' $';
                break;
            case 'NOK' :
                $result = $price . ' kr';
                break;
            case 'NZD' :
                $result = $price . ' $';
                break;
            case 'PHP' :
                $result = $price . ' ₱';
                break;
            case 'PLN' :
                $result = $price . ' zł';
                break;
            case 'GBP' :
                $result = '£' . $price;
                break;
            case 'RON' :
                $result = $price . ' lei';
                break;
            case 'RUB' :
                $result = $price . ' руб.';
                break;
            case 'SGD' :
                $result = $price . ' $';
                break;
            case 'ZAR' :
                $result = $price . ' R';
                break;
            case 'SEK' :
                $result = $price . ' kr';
                break;
            case 'CHF' :
                $result = $price . ' CHF';
                break;
            case 'TWD' :
                $result = $price . ' NT$';
                break;
            case 'THB' :
                $result = $price . ' ฿';
                break;
            case 'TRY' :
                $result = $price . ' TL';
                break;
            case 'USD' :
                $result = '$' . $price;
                break;
        } // switch

        return $result;
    } // formatPrice

    /**
     * @param datetime $dateTime
     * @param string $format
     * @return string
     */
    public function getFormattedDateTime( $dateTime, $format ) {
        if ( $dateTime ) {
            if ( $format == 'time' ) {
                $dateTime = date( $this->get_option( 'time_format' ), strtotime( $dateTime ) );
            } elseif ( $format == 'date' ) {
                $dateTime = date( $this->get_option( 'date_format' ), strtotime( $dateTime ) );
            }
        }

        return $dateTime;
    }

    /**
     * @param null|array $data
     * @param $message
     * @param null|string $_next_day_agenda
     * @return mixed
     */
    public function replace( $data = null, $message = null, $_next_day_agenda = null ) {
        // fields for all notifications
        $company_name      = $this->get_option( 'ab_settings_company_name' ) ?
            $this->get_option( 'ab_settings_company_name' )                             : '';
        $company_logo      = $this->get_option( 'ab_settings_company_logo_url' ) ?
            '<img src="' . $this->get_option( 'ab_settings_company_logo_url' ) . '" />' : '';
        $company_address   = $this->get_option( 'ab_settings_company_address' ) ?
            nl2br( $this->get_option( 'ab_settings_company_address' ) )                 : '';
        $company_phone     = $this->get_option( 'ab_settings_company_phone' ) ?
            $this->get_option( 'ab_settings_company_phone' )                            : '';
        $company_website   = $this->get_option( 'ab_settings_company_website' ) ?
            $this->get_option( 'ab_settings_company_website' )                          : '';
        // fields for custom type notifications
        $next_day_agenda = $_next_day_agenda ? $_next_day_agenda : '';
        $replaced        = '';

        if ( is_array( $data ) ) {
            $replacement = array(
                '[[CLIENT_NAME]]'      => isset( $data[ 'client_name' ] )      ? $data[ 'client_name' ]      : '',
                '[[APPOINTMENT_TIME]]' => isset( $data[ 'appointment_time' ] ) ? $data[ 'appointment_time' ] : '',
                '[[APPOINTMENT_DATE]]' => isset( $data[ 'appointment_date' ] ) ? $data[ 'appointment_date' ] : '',
                '[[SERVICE_NAME]]'     => isset( $data[ 'service_name' ] )     ? $data[ 'service_name' ]     : '',
                '[[SERVICE_PRICE]]'    => isset( $data[ 'service_price' ] )    ? $data[ 'service_price' ]    : $this->formatPrice( 0 ),
                '[[CATEGORY_NAME]]'    => isset( $data[ 'category_name' ] )    ? $data[ 'category_name' ]    : '',
                '[[COMPANY_NAME]]'     => $company_name,
                '[[COMPANY_LOGO]]'     => $company_logo,
                '[[COMPANY_ADDRESS]]'  => $company_address,
                '[[COMPANY_PHONE]]'    => $company_phone,
                '[[COMPANY_WEBSITE]]'  => $company_website,
                '[[NEXT_DAY_AGENDA]]'  => $next_day_agenda,
            );
            $replaced = str_replace( array_keys( $replacement ), array_values( $replacement ), $message );
        }

        return str_replace( 'logo,', 'logo,<br>', $replaced );
    } // replace

    /**
     * @param string $to
     * @param string $subject
     * @param string $message
     * @return bool
     */
    public function send_mail( $to, $subject, $message ) {
        $from_name  = $this->get_option( 'ab_settings_sender_name' );
        $from_email = $this->get_option( 'ab_settings_sender_email' );
        $from = $from_name . ' <' . $from_email . '>';

        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        $headers .= 'From: '.$from.'' . "\r\n";

        return @mail( $to, $subject, $message, $headers );
    } // send_mail

    /**
     * Run each notification-row
     */
    public function run() {
        foreach ( self::$notifications_types as $type => $query ) {
            $notifications = $this->db->run( $query );
            if ( count( $notifications ) ) {
                if ( count( $notifications[ 0 ] ) ) {
                    $this->processNotifications( $notifications, $type );
                }
            }
        }
    } // run

    /**
     * Constructor
     */
    public function __construct() {
        // get DataBase Stats
        $this->stats = new DB_Stats();
        // connect to DataBase
        $this->db = new DB(
            "mysql:dbname={$this->stats->getDbName()};host={$this->stats->getDbHost()}", // DSN
            "{$this->stats->getDbUser()}", // user
            "{$this->stats->getDbPassword()}", // password
            "{$this->stats->getDbCharSet()}" // charset
        );
        // run each notification
        $this->run();
    } // __construct

} // Notifications

$notifications = new Notifications();