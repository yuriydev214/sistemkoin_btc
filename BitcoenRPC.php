Here is the code snippet of sistemkoin.
I would like to discuss more detail via skype.
My skype address is jnx_team@outlook.com.
Please add me as contact and discuss on skype.
Please don't mention about skype on freelancer.com chat session.
It is the violation of site's rule.

<?php
/**
 * iZ³ | Izzzio blockchain - https://izzz.io
 * BitCoen project - https://bitcoen.io
 * @author: Andrey Nedobylsky (admin@twister-vl.ru)
 */
namespace App\Libs;

class BitcoenRPC
{
    private $_baseUrl = 'http://localhost:3001/';
    private $_password = '';


    const METHODS = [
        'getInfo'                     => ['httpMethod' => 'get'],
        'createWallet'                => ['httpMethod' => 'post'],
        'changeWallet'                => ['httpMethod' => 'post'],
        'getTransactions'             => ['httpMethod' => 'get'],
        'createTransaction'           => ['httpMethod' => 'post'],
        'instantTransaction'          => ['httpMethod' => 'post'],
        'getWalletInfo'               => ['httpMethod' => 'get'],
        'getWalletTransactions'       => ['httpMethod' => 'get'],
        'getTransactionByHash'        => ['httpMethod' => 'get'],
        'getTransactionsByBlockIndex' => ['httpMethod' => 'get'],
    ];

    const TINY_ADDRESS_PREFIX = 'BL_';

    const MIL_TO_BEN = 10000000000;

    /**
     * cURL request
     * @param string $method
     * @param string $url
     * @param array $params
     * @return mixed|string
     */
    private static function curlRequest($method = 'get', $url, $params = [], $password = '')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));
        }


        if (!empty($password)) {
            curl_setopt($ch, CURLOPT_USERPWD, '1337' . ":" . $password);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        $response = curl_exec($ch);


        if ($response === false) {
            $response = curl_error($ch);
        }

        curl_close($ch);

        return $response;
    }

    /**
     * Make RPC request
     * @param string $method
     * @param array $params
     * @param string $paramStr
     * @return array|mixed
     * @throws InvalidMethodException
     * @throws ReturnException
     * @throws RpcCallException
     */
    private function request($method, $params = [], $paramStr = '')
    {
        if (empty(self::METHODS[$method])) {
            throw new InvalidMethodException('Invalid method ' . $method);
        }

        $responseBody = self::curlRequest(self::METHODS[$method]['httpMethod'], $this->_baseUrl . $method . $paramStr, $params, $this->_password);
        if (in_array(strtolower($responseBody), ['true', 'false'])) {
            if (strtolower($responseBody) === 'true') {
                return ['status' => 'ok'];
            } else {
                throw new ReturnException('Can\'t call method ' . $method);
            }
        }
        $response = json_decode($responseBody, true);
        if (!is_array($response)) {
            throw new RpcCallException('RPC Error: ' . $responseBody);
        }

        return $response;
    }

    /**
     * BitcoenRPC constructor.
     * @param string $RPCUrl
     * @param string $password
     */
    public function __construct($RPCUrl = 'http://localhost:3001/', $password = '')
    {
        $this->_baseUrl = $RPCUrl;
        $this->_password = $password;

        return $this;
    }

    /**
     * Returns current blockchain status and node info
     * @return mixed
     * @throws InvalidMethodException
     * @throws ReturnException
     * @throws RpcCallException
     */
    public function getInfo()
    {
        return $this->request('getInfo');
    }

    /**
     * Generate and register new wallet with id, block id, private and public keysPair
     * @return mixed
     * @throws InvalidMethodException
     * @throws ReturnException
     * @throws RpcCallException
     */
    public function createWallet()
    {
        return $this->request('createWallet');
    }

    /**
     * Change current wallet for node. The transactions list was recalculated Which can take a long time
     * @param string $id Full wallet address
     * @param string $private Private key
     * @param string $public Public key
     * @return array
     * @throws InvalidMethodException
     * @throws ReturnException
     * @throws RpcCallException
     */
    public function changeWalletByData($id, $private, $public)
    {
        if ($this->getWallet() === $id) {
            return ['status' => 'ok'];
        }

        return $this->request('changeWallet', [
            'id'      => $id,
            'public'  => $public,
            'private' => $private,
            'balance' => 0,
        ]);
    }
