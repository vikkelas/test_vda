<?php

namespace App\Services;

use App\Models\Aircraft;
use App\Models\Airport;
use Exception;
use DateTime;

class FlightService
{
    private Aircraft $aircraft;

    /**
     * @throws Exception
     */
    public function airportStopInformation(string $tail, DateTime $date_from, DateTime $date_to): array
    {
        $flightsFilterDate = $this->getFilterFlights($tail, $date_from, $date_to);
        return $this->getInfoStayAircraft($flightsFilterDate);
    }

    /**
     * @throws Exception
     */
    public function getFilterFlights(string $tail, DateTime $date_from, DateTime $date_to): array
    {
        $this->aircraft = Aircraft::firstWhere('tail', $tail);
        return $this->aircraft
            ->flightsFilter($date_from, $date_to)->get()->toArray();
    }

    public function getInfoAirport($airport_id)
    {
        return Airport::find($airport_id);
    }

    public function getInfoLandingffFirstFlight($airport_id_takeoff, $takeoffDate): array
    {
        return$this->aircraft->flightsRelativeTakeoff($airport_id_takeoff, $takeoffDate)->get()->toArray();
    }

    public function getInfoTakeofLastFlight($airport_id_takeoff, $landingfData): array
    {
        return $this->aircraft->flightsRelativeLanding($airport_id_takeoff, $landingfData)->get()->toArray();
    }

    /**
     * @throws Exception
     */
    public function getInfoStayAircraft($flights): array
    {
        $airport_parking =[];
        foreach ($flights as $key => $flight){
            $airport = $this->getInfoAirport($flight['airport_id1']);
            $airport_parking[$key]["airport_id"] = $airport['id'];
            $airport_parking[$key]["code_iata"] = $airport->code_iata;
            $airport_parking[$key]["code_icao"] = $airport->code_icao;
            $airport_parking[$key]["cargo_load"] = $flight['cargo_load'];
            if($key==0){
                $infoLanding=$this->getInfoLandingffFirstFlight($airport->id, new DateTime($flight['takeoff']));
                if($infoLanding){
                    if(count($infoLanding)){
                        $airport_parking[$key]["cargo_offload"] = $infoLanding[0]['cargo_offload'];
                        $airport_parking[$key]["landing"] = $infoLanding[0]['landing'];
                    }
                }
            }else{
                $airport_parking[$key]["cargo_offload"] = $flights[$key-1]['cargo_offload'];
                $airport_parking[$key]["landing"] = $flights[$key-1]['landing'];
            }
            $airport_parking[$key]["takeoff"] = $flight['takeoff'];
            if($key+1==count($flights)){
                $airportLast = $this->getInfoAirport($flight['airport_id2']);
                $infoLastTakeoff = $this->getInfoTakeofLastFlight($airportLast->id, new DateTime($flight['landing']));
                $airport_parking[$key+1]["airport_id"] = $airport->id;
                $airport_parking[$key+1]["code_iata"] = $airport->code_iata;
                $airport_parking[$key+1]["code_icao"] = $airport->code_icao;
                if(count($infoLastTakeoff)){
                    $airport_parking[$key+1]["cargo_load"] = $infoLastTakeoff[0]['cargo_load'];
                }else{
                    $airport_parking[$key+1]["cargo_load"] = null;
                }
                $airport_parking[$key+1]["cargo_offload"] = $flight['cargo_offload'];
                $airport_parking[$key+1]["landing"] = $flight['landing'];
                if(count($infoLastTakeoff)){
                    $airport_parking[$key+1]["takeoff"] = $infoLastTakeoff[0]['takeoff'];
                }else{
                    $airport_parking[$key+1]["takeoff"] = null;
                }
            }

        }
        return $airport_parking;
    }
}
