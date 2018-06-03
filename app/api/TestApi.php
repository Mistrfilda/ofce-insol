<?php

declare(strict_types = 1);


namespace App\Api;




class TestApi
{
	/**
	 * @return array|mixed[]
	 */
	public function getPersonByBirthId(string $birthId) : array
	{
		$wsdlUrl = "https://isir.justice.cz:8443/isir_cuzk_ws/IsirWsCuzkService?wsdl";
		$soap = new \SoapClient($wsdlUrl);
		try {
			$response = $soap->getIsirWsCuzkData(['rc' => $birthId, 'maxPocetVysledku' => "50", 'filtrAktualniRizeni' => 'T']);
		} catch (\SoapFault $e) {
			throw $e;
		}

		return (array) $response;
	}
}