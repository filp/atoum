<?php

namespace mageekguy\atoum\fcgi;

use
	mageekguy\atoum\fcgi\exceptions,
	mageekguy\atoum\fcgi\records\requests
;

class request implements client\request
{
	protected $persistentConnection = false;
	protected $params = null;
	protected $stdin = null;
	protected $response = null;

	public function __construct($persistentConnection = false)
	{
		$this->persistentConnection = $persistentConnection;
		$this->stdin = new requests\stdin();
		$this->params = new requests\params();
	}

	public function __set($name, $value)
	{
		return (self::cleanName($name) === 'STDIN' ? $this->setStdin($value) : $this->setParam($name, $value));
	}

	public function __get($name)
	{
		return (strtoupper($name) === 'STDIN' ? $this->getStdin() : $this->getParam($name));
	}

	public function __isset($name)
	{
		return (self::cleanName($name) === 'STDIN' ? $this->stdinIsSet() : $this->paramIsSet($name));
	}

	public function __unset($name)
	{
		return (self::cleanName($name) === 'STDIN' ? $this->unsetStdin() : $this->unsetParam($name));
	}

	public function __invoke(client $client)
	{
		return $this->sendWithClient($client);
	}

	public function getRequestId()
	{
		return ($this->response === null ? null : $thsi->response->getRequestId());
	}

	public function setStdin($stdin)
	{
		$this->stdin->setContentData($stdin);

		return $this;
	}

	public function unsetStdin()
	{
		return $this->setStdin('');
	}

	public function stdinIsSet()
	{
		return (sizeof($this->stdin) > 0);
	}

	public function getStdin()
	{
		return $this->stdin->getContentData();
	}

	public function getParam($name)
	{
		return $this->params->{$name};
	}

	public function setParam($name, $value)
	{
		$this->params->{$name} = $value;
	}

	public function unsetParam($name)
	{
		unset($this->params->{$name});

		return $this;
	}

	public function paramIsSet($name)
	{
		return isset($this->params->{$name});
	}

	public function getParams()
	{
		return $this->params->getValues();
	}

	public function connectionIsPersistent()
	{
		return $this->persistentConnection;
	}

	public function reset()
	{
		$this->response = null;

		return $this;
	}

	public function sendWithClient(client $client)
	{
		if (sizeof($this->params) <= 0 && sizeof($this->stdin) <= 0)
		{
			throw new exceptions\runtime('Unable to send an empty request');
		}

		$response = null;

		if ($this->response === null)
		{
			$requestId = $client->getNextRequestId();

			$this->response = new response($requestId);

			$client(new requests\begin(1, $requestId, $this->persistentConnection));

			if (sizeof($this->params) > 0)
			{
				$client($this->params->setRequestId($requestId));
				$client(new requests\params(array(), $requestId));
			}

			if (sizeof($this->stdin) > 0)
			{
				$client($this->stdin->setRequestId($requestId));
				$client(new requests\stdin('', $requestId));
			}

			$client->sendRequest($this);
		}

		return $this;
	}

	public function getResponse()
	{
		return $this->response;
	}

	private static function cleanName($name)
	{
		return strtoupper(trim($name));
	}
}
