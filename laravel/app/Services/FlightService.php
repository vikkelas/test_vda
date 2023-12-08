<?php

namespace App\Services;

use App\Models\Aircraft;
use App\Models\Airport;
use App\Models\Flight;
use Exception;
use DateTime;

class FlightService
{
    private DateTime $date_from;
    private DateTime $date_to;
    private Aircraft $aircraft;

    public function airportStopInformation(string $tail, DateTime $date_from, DateTime $date_to)
    {
        $flightsFilterDate = $this->getFilterFlights($tail, $date_from, $date_to);
        return $this->getInfoStayAircraft($flightsFilterDate);
    }

    /**
     * @throws Exception
     */
    public function getFilterFlights(string $tail, DateTime $date_from, DateTime $date_to): array
    {
        $filterFligts = [];
        $this->aircraft = Aircraft::firstWhere('tail', $tail);
        $this->date_from = $date_from;
        $this->date_to = $date_to;

        $flights_aircraft = $this->aircraft
            ->flights
            ->filter(function ($value, $key){
                $date_takeoff = new DateTime($value->takeoff);
                return $date_takeoff>=$this->date_from&&$date_takeoff<=$this->date_to;
            })
            ->sortBy(['takeoff', 'asc']);
        foreach ($flights_aircraft as $value){
            $filterFligts[] = $value;
        }
        return $filterFligts;
    }

    public function getInfoAirport($airport_id)
    {
        return Airport::find($airport_id);
    }

    public function getInfoLandingffFirstFlight($airport_id_takeoff, $takeoffDate)
    {
        $flights = Flight::all();
        return  $flights
            ->where('aircraft_id', $this->aircraft->id)
            ->where('airport_id2', $airport_id_takeoff)
            ->firstWhere('landing', '<=' ,$takeoffDate);
    }

    public function getInfoTakeofLastFlight($airport_id_takeoff, $takeoffData)
    {
        $flights = Flight::all();
        return  $flights
            ->where('aircraft_id', $this->aircraft->id)
            ->where('airport_id2', $airport_id_takeoff)
            ->firstWhere('landing', '>=' ,$takeoffData);
    }

    public function getInfoStayAircraft($flights): array
    {
        $airport_parking =[];
        foreach ($flights as $key => $flight){
            $airport = $this->getInfoAirport($flight->airport_id1);
            $airport_parking[$key]["airport_id"] = $airport->id;
            $airport_parking[$key]["code_iata"] = $airport->code_iata;
            $airport_parking[$key]["code_icao"] = $airport->code_icao;
            $airport_parking[$key]["cargo_load"] = $flight->cargo_load;
            if($key==0){
                $infoLanding=$this->getInfoLandingffFirstFlight($airport->id, $flight->takeoff);
                if($infoLanding){
                    $airport_parking[$key]["cargo_offload"] = $infoLanding->cargo_offload;
                    $airport_parking[$key]["landing"] = $infoLanding->landing;
                }
            }else{
                $airport_parking[$key]["cargo_offload"] = $flights[$key-1]->cargo_offload;
                $airport_parking[$key]["landing"] = $flights[$key-1]->landing;
            }
            $airport_parking[$key]["takeoff"] = $flight->takeoff;
            if($key+1==count($flights)){
                $airportLast = $this->getInfoAirport($flight->airport_id2);
                $infoLastTakeoff = $this->getInfoTakeofLastFlight($airportLast->id, $flight->landing);
                $airport_parking[$key+1]["airport_id"] = $airport->id;
                $airport_parking[$key+1]["code_iata"] = $airport->code_iata;
                $airport_parking[$key+1]["code_icao"] = $airport->code_icao;
                if($infoLastTakeoff){
                    $airport_parking[$key+1]["cargo_load"] = $infoLastTakeoff->cargo_load;
                }else{
                    $airport_parking[$key+1]["cargo_load"] = null;
                }
                $airport_parking[$key+1]["cargo_offload"] = $flight->cargo_offload;
                $airport_parking[$key+1]["landing"] = $flight->landing;
                if($infoLastTakeoff){
                    $airport_parking[$key+1]["takeoff"] = $infoLastTakeoff->takeoff;
                }else{
                    $airport_parking[$key+1]["takeoff"] = null;
                }
            }

        }
        return $airport_parking;
    }
}
