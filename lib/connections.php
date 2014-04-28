    public function searchLightHouseRecommendations($account_info,$profile,$track_error = true, $limit = 100, $filter_connections = true,$filter_requests = true)
    {
        $teachers = array();
       if ($account_info['user_id'] = 6446538){
           return $teachers;
       }


        //make sure the user belongs to a school
       // if (!empty($profile['school_id'])) {
			  $school = 'ds';
            if(!empty($school)){

                $school_id = 128708;
                $district_id = 17900;

                //set a cache key so that we can store the high scorers per district or school id
                $cache_key = 'school_id ' . $school_id . ' or district_id '. $district_id . ' last updated';
                $cache_handler = MemcacheHandler::getInstance();
                $teachers_ranked_by_scores = $cache_handler->load($cache_key);
                $cache_staff_by_score = false;
                $teachers_ranked_by_scores = false;
                if(empty($teachers_ranked_by_scores)){
                    $cache_staff_by_score = true;
                    // Fetch suggested teachers from Schools first
                    $staff_members = Schools::getInstance()->getStaff($school_id, null, $limit, array(), false);
                    error_log(' satff: ' . print_r($staff_members, true));


                    // if we can't find any teachers from the same school, check the district
                    if (empty($staff_members) && !empty($school['district_id'])) {
                        $staff_members = Districts::getInstance()->getDistrictStaff($school['district_id'], null, $limit);
                    }
                   /* try{
                        //look up the scores of each teacher, and have them ordered by score
                        $teachers_ranked_by_scores = EdmodoScores::getInstance()->getScoresByIds($staff_members, 100);
                    }
                    catch (Exception $e){
                        // general exception
                        error_log(__CLASS__ . '::' . __FUNCTION__ . '() !WARNING: Problem attempting to get user_score for given array of staff_ids '.$e->getMessage());
                        return array();
                    }*/
                }
                $teachers_ranked_by_scores = $staff_members;

                //set the results of the teachers and scores into cache for 24 hours
                if ($cache_staff_by_score && $cache_handler->cachingAvailable()){
                    $cache_handler->save($teachers_ranked_by_scores,$cache_key, 86400);
                }

                // Let's filter out:

                // 1) Pending Connection Requests
                if($filter_requests)
                {
                    $pending_connection_requests = ConnectionRequests::getInstance()->getConnectionRequestUserIdsByRequester($account_info['user_id']);
                    $pending_connection_requests = array_keys(ArrayHelper::rekey($pending_connection_requests, 'requestee_id'));
                }
                else
                {
                     $pending_connection_requests = array ();
                }

                // 2) Already Connected users
                if($filter_connections)
                {
                    $connected_user_ids = Connections::getInstance()->getConnectedUserIds($account_info['user_id']);
                }
                else
                {
                    //On some cases the process might benefit of showing images of people already connected, so don't filter out
                    $connected_user_ids = array ();
                }

                // 3) Declined Suggestions
                $declined_ids = DeclinedSuggestions::getInstance()->getDeclinedSuggestionsByUserId($account_info['user_id'], DeclinedSuggestions::TYPE_USER);
                $declined_ids = array_keys(ArrayHelper::rekey($declined_ids, 'suggested_id'));

                $excluded_user_ids = array_merge(array($account_info['user_id']), $connected_user_ids, $pending_connection_requests, $declined_ids);

                $teacher_user_ids = array_keys($teachers_ranked_by_scores);

                // 4) Finally, remove those that have blocked connections
                if (!empty($teacher_user_ids)) {
                    $teacher_privacy_settings = PrivacySettings::getInstance()->getPrivacySettingsForUsers($teacher_user_ids);
                    foreach ($teachers_ranked_by_scores as $index => $teacher_ranked_by_score) {
                        $settings = $teacher_privacy_settings[$teacher_ranked_by_score['user_id']];
                        if (!empty($settings) && 1 == $settings['block_connections']) {
                            unset($teachers_ranked_by_scores[$index]);
                        }
                    }
                }

                //compare the results of the returned teachers with the excluded ones to determine who to omit
                $teacher_ids_to_exclude = array_intersect($teacher_user_ids, $excluded_user_ids);

                //remove the exclusions from the original list
                if (!empty($teacher_ids_to_exclude)){
                    foreach ($teacher_ids_to_exclude as $teacher_id_to_exclude){

                    unset ($teachers_ranked_by_scores[$teacher_id_to_exclude]);
                    }
                }

                //set our final result (applicable users to return)
                $teachers = $teachers_ranked_by_scores;

                // If there aren't any teachers to recommend, we need to know.
                if (empty($teachers) && $track_error) {
                    ActionsTracking::getInstance()->insert(array(
                    ActionsTrackingConstants::USER_AGENT => getenv('HTTP_USER_AGENT'),
                    ActionsTrackingConstants::EVENT_TYPE => 'nag-lht',
                    ActionsTrackingConstants::EVENT_NAME => 'no reco: no teachers from school'
                  ));
                }
            //}
        }
        return $teachers;
    }
