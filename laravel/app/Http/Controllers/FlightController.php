<?php

namespace App\Http\Controllers;

use App\Services\FlightService;
use DateTime;
use Exception;
use Illuminate\Http\Request;

class FlightController extends Controller
{
    public \DateTime $date_from;
    public \DateTime $date_to;
    public FlightService $flight_service;

    public function __construct()
    {
        $this->flight_service = new FlightService();
    }

    /**
     * @throws Exception
     */
    public function getIntervalFlights(Request $request): array
    {
        $tail = $request->query('tail');
        $this->date_from = new DateTime($request->query('date_from'));
        $this->date_to = new DateTime($request->query('date_to'));
        return $this->flight_service->airportStopInformation($tail, $this->date_from, $this->date_to);

    }
}
