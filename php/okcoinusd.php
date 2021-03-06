<?php

namespace ccxt;

// PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
// https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

use Exception as Exception; // a common import

class okcoinusd extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'okcoinusd',
            'name' => 'OKCoin USD',
            'countries' => array ( 'CN', 'US' ),
            'version' => 'v1',
            'rateLimit' => 1000, // up to 3000 requests per 5 minutes ≈ 600 requests per minute ≈ 10 requests per second ≈ 100 ms
            'has' => array (
                'CORS' => false,
                'fetchOHLCV' => true,
                'fetchOrder' => true,
                'fetchOrders' => false,
                'fetchOpenOrders' => true,
                'fetchClosedOrders' => true,
                'withdraw' => true,
                'futures' => false,
            ),
            'extension' => '.do', // appended to endpoint URL
            'timeframes' => array (
                '1m' => '1min',
                '3m' => '3min',
                '5m' => '5min',
                '15m' => '15min',
                '30m' => '30min',
                '1h' => '1hour',
                '2h' => '2hour',
                '4h' => '4hour',
                '6h' => '6hour',
                '12h' => '12hour',
                '1d' => '1day',
                '3d' => '3day',
                '1w' => '1week',
            ),
            'api' => array (
                'web' => array (
                    'get' => array (
                        'markets/currencies',
                        'markets/products',
                    ),
                ),
                'public' => array (
                    'get' => array (
                        'depth',
                        'exchange_rate',
                        'future_depth',
                        'future_estimated_price',
                        'future_hold_amount',
                        'future_index',
                        'future_kline',
                        'future_price_limit',
                        'future_ticker',
                        'future_trades',
                        'kline',
                        'otcs',
                        'ticker',
                        'tickers',
                        'trades',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'account_records',
                        'batch_trade',
                        'borrow_money',
                        'borrow_order_info',
                        'borrows_info',
                        'cancel_borrow',
                        'cancel_order',
                        'cancel_otc_order',
                        'cancel_withdraw',
                        'future_batch_trade',
                        'future_cancel',
                        'future_devolve',
                        'future_explosive',
                        'future_order_info',
                        'future_orders_info',
                        'future_position',
                        'future_position_4fix',
                        'future_trade',
                        'future_trades_history',
                        'future_userinfo',
                        'future_userinfo_4fix',
                        'lend_depth',
                        'order_fee',
                        'order_history',
                        'order_info',
                        'orders_info',
                        'otc_order_history',
                        'otc_order_info',
                        'repayment',
                        'submit_otc_order',
                        'trade',
                        'trade_history',
                        'trade_otc_order',
                        'withdraw',
                        'withdraw_info',
                        'unrepayments_info',
                        'userinfo',
                    ),
                ),
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/27766791-89ffb502-5ee5-11e7-8a5b-c5950b68ac65.jpg',
                'api' => array (
                    'web' => 'https://www.okcoin.com/v2',
                    'public' => 'https://www.okcoin.com/api',
                    'private' => 'https://www.okcoin.com/api',
                ),
                'www' => 'https://www.okcoin.com',
                'doc' => array (
                    'https://www.okcoin.com/rest_getStarted.html',
                    'https://www.npmjs.com/package/okcoin.com',
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'taker' => 0.002,
                    'maker' => 0.002,
                ),
            ),
            'exceptions' => array (
                '1009' => '\\ccxt\\OrderNotFound', // for spot markets, cancelling closed order
                '1051' => '\\ccxt\\OrderNotFound', // for spot markets, cancelling "just closed" order
                '20015' => '\\ccxt\\OrderNotFound', // for future markets
                '1013' => '\\ccxt\\InvalidOrder', // no contract type (PR-1101)
                '1027' => '\\ccxt\\InvalidOrder', // createLimitBuyOrder(symbol, 0, 0) => Incorrect parameter may exceeded limits
                '1002' => '\\ccxt\\InsufficientFunds', // "The transaction amount exceed the balance"
                '1050' => '\\ccxt\\InvalidOrder', // returned when trying to cancel an order that was filled or canceled previously
                '10000' => '\\ccxt\\ExchangeError', // createLimitBuyOrder(symbol, null, null)
                '10005' => '\\ccxt\\AuthenticationError', // bad apiKey
                '10008' => '\\ccxt\\ExchangeError', // Illegal URL parameter
            ),
            'options' => array (
                'warnOnFetchOHLCVLimitArgument' => true,
            ),
        ));
    }

    public function fetch_markets () {
        $response = $this->webGetMarketsProducts ();
        $markets = $response['data'];
        $result = array ();
        $futureMarkets = array (
            'BCH/USD' => true,
            'BTC/USD' => true,
            'ETC/USD' => true,
            'ETH/USD' => true,
            'LTC/USD' => true,
            'XRP/USD' => true,
            'EOS/USD' => true,
            'BTG/USD' => true,
        );
        for ($i = 0; $i < count ($markets); $i++) {
            $id = $markets[$i]['symbol'];
            list ($baseId, $quoteId) = explode ('_', $id);
            $baseIdUppercase = strtoupper ($baseId);
            $quoteIdUppercase = strtoupper ($quoteId);
            $base = $this->common_currency_code($baseIdUppercase);
            $quote = $this->common_currency_code($quoteIdUppercase);
            $symbol = $base . '/' . $quote;
            $precision = array (
                'amount' => $markets[$i]['maxSizeDigit'],
                'price' => $markets[$i]['maxPriceDigit'],
            );
            $lot = pow (10, -$precision['amount']);
            $minAmount = $markets[$i]['minTradeSize'];
            $minPrice = pow (10, -$precision['price']);
            $active = ($markets[$i]['online'] !== 0);
            $market = array_merge ($this->fees['trading'], array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'info' => $markets[$i],
                'type' => 'spot',
                'spot' => true,
                'future' => false,
                'lot' => $lot,
                'active' => $active,
                'precision' => $precision,
                'limits' => array (
                    'amount' => array (
                        'min' => $minAmount,
                        'max' => null,
                    ),
                    'price' => array (
                        'min' => $minPrice,
                        'max' => null,
                    ),
                    'cost' => array (
                        'min' => $minAmount * $minPrice,
                        'max' => null,
                    ),
                ),
            ));
            $result[] = $market;
            $futureQuote = ($market['quote'] === 'USDT') ? 'USD' : $market['quote'];
            $futureSymbol = $market['base'] . '/' . $futureQuote;
            if (($this->has['futures']) && (is_array ($futureMarkets) && array_key_exists ($futureSymbol, $futureMarkets))) {
                $result[] = array_merge ($market, array (
                    'quote' => 'USD',
                    'symbol' => $market['base'] . '/USD',
                    'id' => str_replace ('usdt', 'usd', $market['id']),
                    'quoteId' => str_replace ('usdt', 'usd', $market['quoteId']),
                    'type' => 'future',
                    'spot' => false,
                    'future' => true,
                ));
            }
        }
        return $result;
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $method = 'publicGet';
        $request = array (
            'symbol' => $market['id'],
        );
        if ($limit !== null)
            $request['size'] = $limit;
        if ($market['future']) {
            $method .= 'Future';
            $request['contract_type'] = 'this_week'; // next_week, quarter
        }
        $method .= 'Depth';
        $orderbook = $this->$method (array_merge ($request, $params));
        return $this->parse_order_book($orderbook);
    }

    public function parse_ticker ($ticker, $market = null) {
        $timestamp = $ticker['timestamp'];
        $symbol = null;
        if (!$market) {
            if (is_array ($ticker) && array_key_exists ('symbol', $ticker)) {
                $marketId = $ticker['symbol'];
                if (is_array ($this->markets_by_id) && array_key_exists ($marketId, $this->markets_by_id))
                    $market = $this->markets_by_id[$marketId];
            }
        }
        if ($market)
            $symbol = $market['symbol'];
        $last = floatval ($ticker['last']);
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => floatval ($ticker['high']),
            'low' => floatval ($ticker['low']),
            'bid' => floatval ($ticker['buy']),
            'bidVolume' => null,
            'ask' => floatval ($ticker['sell']),
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => floatval ($ticker['vol']),
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $method = 'publicGet';
        $request = array (
            'symbol' => $market['id'],
        );
        if ($market['future']) {
            $method .= 'Future';
            $request['contract_type'] = 'this_week'; // next_week, quarter
        }
        $method .= 'Ticker';
        $response = $this->$method (array_merge ($request, $params));
        $ticker = $this->safe_value($response, 'ticker');
        if ($ticker === null)
            throw new ExchangeError ($this->id . ' fetchTicker returned an empty $response => ' . $this->json ($response));
        $timestamp = $this->safe_integer($response, 'date');
        if ($timestamp !== null) {
            $timestamp *= 1000;
            $ticker = array_merge ($ticker, array ( 'timestamp' => $timestamp ));
        }
        return $this->parse_ticker($ticker, $market);
    }

    public function parse_trade ($trade, $market = null) {
        $symbol = null;
        if ($market)
            $symbol = $market['symbol'];
        return array (
            'info' => $trade,
            'timestamp' => $trade['date_ms'],
            'datetime' => $this->iso8601 ($trade['date_ms']),
            'symbol' => $symbol,
            'id' => (string) $trade['tid'],
            'order' => null,
            'type' => null,
            'side' => $trade['type'],
            'price' => floatval ($trade['price']),
            'amount' => floatval ($trade['amount']),
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $method = 'publicGet';
        $request = array (
            'symbol' => $market['id'],
        );
        if ($market['future']) {
            $method .= 'Future';
            $request['contract_type'] = 'this_week'; // next_week, quarter
        }
        $method .= 'Trades';
        $response = $this->$method (array_merge ($request, $params));
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function fetch_ohlcv ($symbol, $timeframe = '1m', $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $method = 'publicGet';
        $request = array (
            'symbol' => $market['id'],
            'type' => $this->timeframes[$timeframe],
        );
        if ($market['future']) {
            $method .= 'Future';
            $request['contract_type'] = 'this_week'; // next_week, quarter
        }
        $method .= 'Kline';
        if ($limit !== null) {
            if ($this->options['warnOnFetchOHLCVLimitArgument'])
                throw new ExchangeError ($this->id . ' fetchOHLCV counts "$limit" candles from current time backwards, therefore the "$limit" argument for ' . $this->id . ' is disabled. Set ' . $this->id . '.options["warnOnFetchOHLCVLimitArgument"] = false to suppress this warning message.');
            $request['size'] = intval ($limit); // max is 1440 candles
        }
        if ($since !== null)
            $request['since'] = $since;
        else
            $request['since'] = $this->milliseconds () - 86400000; // last 24 hours
        $response = $this->$method (array_merge ($request, $params));
        return $this->parse_ohlcvs($response, $market, $timeframe, $since, $limit);
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostUserinfo ();
        $balances = $response['info']['funds'];
        $result = array ( 'info' => $response );
        $ids = is_array ($this->currencies_by_id) ? array_keys ($this->currencies_by_id) : array ();
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            $code = $this->currencies_by_id[$id]['code'];
            $account = $this->account ();
            $account['free'] = $this->safe_float($balances['free'], $id, 0.0);
            $account['used'] = $this->safe_float($balances['freezed'], $id, 0.0);
            $account['total'] = $this->sum ($account['free'], $account['used']);
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $method = 'privatePost';
        $order = array (
            'symbol' => $market['id'],
            'type' => $side,
        );
        if ($market['future']) {
            $method .= 'Future';
            $order = array_merge ($order, array (
                'contract_type' => 'this_week', // next_week, quarter
                'match_price' => 0, // match best counter party $price? 0 or 1, ignores $price if 1
                'lever_rate' => 10, // leverage rate value => 10 or 20 (10 by default)
                'price' => $price,
                'amount' => $amount,
            ));
        } else {
            if ($type === 'limit') {
                $order['price'] = $price;
                $order['amount'] = $amount;
            } else {
                $order['type'] .= '_market';
                if ($side === 'buy') {
                    $order['price'] = $this->safe_float($params, 'cost');
                    if (!$order['price'])
                        throw new ExchangeError ($this->id . ' $market buy orders require an additional cost parameter, cost = $price * amount');
                } else {
                    $order['amount'] = $amount;
                }
            }
        }
        $params = $this->omit ($params, 'cost');
        $method .= 'Trade';
        $response = $this->$method (array_merge ($order, $params));
        return array (
            'info' => $response,
            'id' => (string) $response['order_id'],
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        if (!$symbol)
            throw new ExchangeError ($this->id . ' cancelOrder() requires a $symbol argument');
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
            'order_id' => $id,
        );
        $method = 'privatePost';
        if ($market['future']) {
            $method .= 'FutureCancel';
            $request['contract_type'] = 'this_week'; // next_week, quarter
        } else {
            $method .= 'CancelOrder';
        }
        $response = $this->$method (array_merge ($request, $params));
        return $response;
    }

    public function parse_order_status ($status) {
        if ($status === -1)
            return 'canceled';
        if ($status === 0)
            return 'open';
        if ($status === 1)
            return 'open';
        if ($status === 2)
            return 'closed';
        if ($status === 4)
            return 'canceled';
        return $status;
    }

    public function parse_order_side ($side) {
        if ($side === 1)
            return 'buy'; // open long position
        if ($side === 2)
            return 'sell'; // open short position
        if ($side === 3)
            return 'sell'; // liquidate long position
        if ($side === 4)
            return 'buy'; // liquidate short position
        return $side;
    }

    public function parse_order ($order, $market = null) {
        $side = null;
        $type = null;
        if (is_array ($order) && array_key_exists ('type', $order)) {
            if (($order['type'] === 'buy') || ($order['type'] === 'sell')) {
                $side = $order['type'];
                $type = 'limit';
            } else if ($order['type'] === 'buy_market') {
                $side = 'buy';
                $type = 'market';
            } else if ($order['type'] === 'sell_market') {
                $side = 'sell';
                $type = 'market';
            } else {
                $side = $this->parse_order_side ($order['type']);
                if ((is_array ($order) && array_key_exists ('contract_name', $order)) || (is_array ($order) && array_key_exists ('lever_rate', $order)))
                    $type = 'margin';
            }
        }
        $status = $this->parse_order_status($order['status']);
        $symbol = null;
        if (!$market) {
            if (is_array ($order) && array_key_exists ('symbol', $order))
                if (is_array ($this->markets_by_id) && array_key_exists ($order['symbol'], $this->markets_by_id))
                    $market = $this->markets_by_id[$order['symbol']];
        }
        if ($market)
            $symbol = $market['symbol'];
        $timestamp = null;
        $createDateField = $this->get_create_date_field ();
        if (is_array ($order) && array_key_exists ($createDateField, $order))
            $timestamp = $order[$createDateField];
        $amount = $order['amount'];
        $filled = $order['deal_amount'];
        $remaining = $amount - $filled;
        $average = $this->safe_float($order, 'avg_price');
        // https://github.com/ccxt/ccxt/issues/2452
        $average = $this->safe_float($order, 'price_avg', $average);
        $cost = $average * $filled;
        $result = array (
            'info' => $order,
            'id' => (string) $order['order_id'],
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $order['price'],
            'average' => $average,
            'cost' => $cost,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'status' => $status,
            'fee' => null,
        );
        return $result;
    }

    public function get_create_date_field () {
        // needed for derived exchanges
        // allcoin typo create_data instead of create_date
        return 'create_date';
    }

    public function get_orders_field () {
        // needed for derived exchanges
        // allcoin typo order instead of orders (expected based on their API docs)
        return 'orders';
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        if (!$symbol)
            throw new ExchangeError ($this->id . ' fetchOrder requires a $symbol parameter');
        $this->load_markets();
        $market = $this->market ($symbol);
        $method = 'privatePost';
        $request = array (
            'order_id' => $id,
            'symbol' => $market['id'],
            // 'status' => 0, // 0 for unfilled orders, 1 for filled orders
            // 'current_page' => 1, // current page number
            // 'page_length' => 200, // number of orders returned per page, maximum 200
        );
        if ($market['future']) {
            $method .= 'Future';
            $request['contract_type'] = 'this_week'; // next_week, quarter
        }
        $method .= 'OrderInfo';
        $response = $this->$method (array_merge ($request, $params));
        $ordersField = $this->get_orders_field ();
        $numOrders = is_array ($response[$ordersField]) ? count ($response[$ordersField]) : 0;
        if ($numOrders > 0)
            return $this->parse_order($response[$ordersField][0]);
        throw new OrderNotFound ($this->id . ' order ' . $id . ' not found');
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        if (!$symbol)
            throw new ExchangeError ($this->id . ' fetchOrders requires a $symbol parameter');
        $this->load_markets();
        $market = $this->market ($symbol);
        $method = 'privatePost';
        $request = array (
            'symbol' => $market['id'],
        );
        $order_id_in_params = (is_array ($params) && array_key_exists ('order_id', $params));
        if ($market['future']) {
            $method .= 'FutureOrdersInfo';
            $request['contract_type'] = 'this_week'; // next_week, quarter
            if (!$order_id_in_params)
                throw new ExchangeError ($this->id . ' fetchOrders() requires order_id param for futures $market ' . $symbol . ' (a string of one or more order ids, comma-separated)');
        } else {
            $status = null;
            if (is_array ($params) && array_key_exists ('type', $params)) {
                $status = $params['type'];
            } else if (is_array ($params) && array_key_exists ('status', $params)) {
                $status = $params['status'];
            } else {
                $name = $order_id_in_params ? 'type' : 'status';
                throw new ExchangeError ($this->id . ' fetchOrders() requires ' . $name . ' param for spot $market ' . $symbol . ' (0 - for unfilled orders, 1 - for filled/canceled orders)');
            }
            if ($order_id_in_params) {
                $method .= 'OrdersInfo';
                $request = array_merge ($request, array (
                    'type' => $status,
                ));
            } else {
                $method .= 'OrderHistory';
                $request = array_merge ($request, array (
                    'status' => $status,
                    'current_page' => 1, // current page number
                    'page_length' => 200, // number of orders returned per page, maximum 200
                ));
            }
            $params = $this->omit ($params, array ( 'type', 'status' ));
        }
        $response = $this->$method (array_merge ($request, $params));
        $ordersField = $this->get_orders_field ();
        return $this->parse_orders($response[$ordersField], $market, $since, $limit);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $open = 0; // 0 for unfilled orders, 1 for filled orders
        return $this->fetch_orders($symbol, null, null, array_merge (array (
            'status' => $open,
        ), $params));
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $closed = 1; // 0 for unfilled $orders, 1 for filled $orders
        $orders = $this->fetch_orders($symbol, null, null, array_merge (array (
            'status' => $closed,
        ), $params));
        return $this->filter_by($orders, 'status', 'closed');
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        $this->load_markets();
        $currency = $this->currency ($code);
        // if ($amount < 0.01)
        //     throw new ExchangeError ($this->id . ' withdraw() requires $amount > 0.01');
        // for some reason they require to supply a pair of currencies for withdrawing one $currency
        $currencyId = $currency['id'] . '_usd';
        $request = array (
            'symbol' => $currencyId,
            'withdraw_address' => $address,
            'withdraw_amount' => $amount,
            'target' => 'address', // or okcn, okcom, okex
        );
        $query = $params;
        if (is_array ($query) && array_key_exists ('chargefee', $query)) {
            $request['chargefee'] = $query['chargefee'];
            $query = $this->omit ($query, 'chargefee');
        } else {
            throw new ExchangeError ($this->id . ' withdraw() requires a `chargefee` parameter');
        }
        if ($this->password) {
            $request['trade_pwd'] = $this->password;
        } else if (is_array ($query) && array_key_exists ('password', $query)) {
            $request['trade_pwd'] = $query['password'];
            $query = $this->omit ($query, 'password');
        } else if (is_array ($query) && array_key_exists ('trade_pwd', $query)) {
            $request['trade_pwd'] = $query['trade_pwd'];
            $query = $this->omit ($query, 'trade_pwd');
        }
        $passwordInRequest = (is_array ($request) && array_key_exists ('trade_pwd', $request));
        if (!$passwordInRequest)
            throw new ExchangeError ($this->id . ' withdraw() requires $this->password set on the exchange instance or a password / trade_pwd parameter');
        $response = $this->privatePostWithdraw (array_merge ($request, $query));
        return array (
            'info' => $response,
            'id' => $this->safe_string($response, 'withdraw_id'),
        );
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = '/';
        if ($api !== 'web')
            $url .= $this->version . '/';
        $url .= $path . $this->extension;
        if ($api === 'private') {
            $this->check_required_credentials();
            $query = $this->keysort (array_merge (array (
                'api_key' => $this->apiKey,
            ), $params));
            // secret key must be at the end of $query
            $queryString = $this->rawencode ($query) . '&secret_key=' . $this->secret;
            $query['sign'] = strtoupper ($this->hash ($this->encode ($queryString)));
            $body = $this->urlencode ($query);
            $headers = array ( 'Content-Type' => 'application/x-www-form-urlencoded' );
        } else {
            if ($params)
                $url .= '?' . $this->urlencode ($params);
        }
        $url = $this->urls['api'][$api] . $url;
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($code, $reason, $url, $method, $headers, $body) {
        if (strlen ($body) < 2)
            return; // fallback to default $error handler
        if ($body[0] === '{') {
            $response = json_decode ($body, $as_associative_array = true);
            if (is_array ($response) && array_key_exists ('error_code', $response)) {
                $error = $this->safe_string($response, 'error_code');
                $message = $this->id . ' ' . $this->json ($response);
                if (is_array ($this->exceptions) && array_key_exists ($error, $this->exceptions)) {
                    $ExceptionClass = $this->exceptions[$error];
                    throw new $ExceptionClass ($message);
                } else {
                    throw new ExchangeError ($message);
                }
            }
            if (is_array ($response) && array_key_exists ('result', $response))
                if (!$response['result'])
                    throw new ExchangeError ($this->id . ' ' . $this->json ($response));
        }
    }
}
