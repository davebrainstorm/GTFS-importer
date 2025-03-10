<?php
class GTFS_Journey_Planner_API extends GTFS_API_Connector {

    public function get_live_arrivals($stop_id) {
        $url = 'https://api.example.com/realtime/arrivals?stop=' . $stop_id;
        $data = $this->request($url);
        return json_decode($data, true);
    }
}
