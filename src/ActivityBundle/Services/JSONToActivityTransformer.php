<?php

namespace ActivityBundle\Services;

use ActivityBundle\Entity\Activity;

class JSONToActivityTransformer
{
    protected static function getAutoFillFields()
    {
        return array(
            'id' => 'resourceId',
            'name' => 'name',
            'start_date' => 'startDate',
            'start_date_local' => 'startDateLocal',
            'elapsed_time' => 'elapsedTime',
            'manual' => 'manual',
        );
    }

    public function transform(array $input = array())
    {
        $activity = new Activity();

        // Set the auto-apply fields
        $this->applyAutoFields($activity, $input);

        // Set polyline
        $this->setPolyline($activity, $input);

        // Set lat/lng stuff
        $this->setLatLng($activity, $input);

        return $activity;
    }

    protected function applyAutoFields(Activity $activity, array $input = array())
    {
        $autoFields = self::getAutoFillFields();

        foreach ($autoFields as $jsonField => $activityField) {
            $setter = sprintf('set%s', $activityField);

            // Check if there's a value for this
            if (array_key_exists($jsonField, $input) && null !== $input[$jsonField] && strlen($input[$jsonField])) {
                // There's a value
                $value = $input[$jsonField];

                // Check if there's a setter
                if (method_exists($activity, $setter)) {
                    $activity->$setter($value);
                } else {
                    throw new \Exception(sprintf('Could not find setter for field %s', $activityField));
                }
            }
        }

        return $activity;
    }

    protected function setPolyline(Activity $activity, array $input = array())
    {
        if (array_key_exists('map', $input) && is_array($input['map'])) {
            $map = $input['map'];

            if (array_key_exists('summary_polyline', $map)) {
                $activity->setPolyline($map['summary_polyline']);
            }
        }
    }

    protected function setLatLng(Activity $activity, array $input = array())
    {
        if (array_key_exists('start_latlng', $input) && is_array($input['start_latlng'])) {
            $activity->setStartLat($input['start_latlng'][0]);
            $activity->setStartLng($input['start_latlng'][1]);
        }

        if (array_key_exists('end_latlng', $input) && is_array($input['end_latlng'])) {
            $activity->setEndLat($input['end_latlng'][0]);
            $activity->setEndLng($input['end_latlng'][1]);
        }
    }
}
