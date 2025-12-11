<?php

namespace App\Helpers;

use App\Models\SubmissionTime;
use App\Models\ChecklistTask;
use App\Models\Designation;
use App\Models\TicketMember;
use App\Jobs\TicketMail;
use \Carbon\Carbon;

class Helper {

    public static $status = [
        'pending' => 0,
        'in-progress' => 1,
        'in-verification' => 2,
        'completed' => 3
    ];

    public static $roles = [
        'admin' => 1,
        'store-manager' => 2,
        'store-employee' => 3,
        'store-cashier' => 4,
        'corporate-office-manager' => 5,
        'divisional-operations-manager' => 6,
        'head-of-department' => 7,
        'vice-president' => 8,
        'director' => 9,
        'operations-manager' => 10,
        'store-phone' => 11,
        'employee' => 12
    ];

    public static $rolesKeys = [
        1 => 'admin',
        2 => 'store-manager',
        3 => 'store-employee',
        4 => 'store-cashier',
        5 => 'corporate-office-manager',
        6 => 'divisional-operations-manager',
        7 => 'head-of-department',
        8 => 'vice-president',
        9 => 'director',
        10 => 'operations-manager',
        11 => 'store-phone',
        12 => 'employee'
    ];

    public static $notificationTemplatePlaceholders = [
        '{$name}' => 'Name',
        '{$username}' => 'Username',
        '{$phone_number}' => 'Phone Number',
        '{$email}' => 'Email',
        '{$branch_name}' => 'Branch Name',
        '{$checklist_name}' => 'Checklist Name',
        '{$section_name}' => 'Section Name'
    ];

    public static $storeCheckLists = [
        100,
        101,
        102,
        103,
        104,
        105,
        106,
        107
    ];

    public static $frequency = [
        'Every Hour',
        'Every N Hours',
        'Daily',
        'Every N Days',
        'Weekly',
        'Biweekly',
        'Monthly',
        'Bimonthly',
        'Quarterly',
        'Semi Anually',
        'Anually',
        'Specific Week Days',
        'Once'
    ];

    public static $namesToBeIgnored = [
        'c1-nos-2000',
        'c1-amount-2000',
        'c1-nos-500',
        'c1-amount-500',
        'c1-nos-200',
        'c1-amount-200',
        'c1-nos-100',
        'c1-amount-100',
        'c1-nos-50',
        'c1-amount-50',
        'c1-nos-20',
        'c1-amount-20',
        'c1-nos-10',
        'c1-amount-10',
        'c1-nos-5',
        'c1-amount-5',
        'c1-nos-2',
        'c1-amount-2',
        'c1-nos-1',
        'c1-amount-1',
        'cash-zreport-c1',
        'cash-actual-c1',
        'cash-difference-c1',
        'cash-source',
        'cash-reason',
        'card-zreport-c1',
        'card-actual-c1',
        'card-difference-c1',
        'card-source',
        'card-reason',
        'swiggy-zreport-c1',
        'swiggy-actual-c1',
        'swiggy-difference-c1',
        'swiggy-source',
        'swiggy-reason',
        'zomato-zreport-c1',
        'zomato-actual-c1',
        'zomato-difference-c1',
        'zomato-source',
        'zomato-reason',
        'paytm-zreport-c1',
        'paytm-actual-c1',
        'paytm-difference-c1',
        'paytm-source',
        'paytm-reason',
        'bharatpe-zreport-c1',
        'bharatpe-actual-c1',
        'bharatpe-difference-c1',
        'bharatpe-source',
        'bharatpe-reason',
        'phonepe-zreport-c1',
        'phonepe-actual-c1',
        'phonepe-difference-c1',
        'phonepe-source',
        'phonepe-reason',
        'vouchers-zreport-c1',
        'vouchers-actual-c1',
        'vouchers-difference-c1',
        'vouchers-source',
        'vouchers-reason',
        'creditsale-zreport-c1',
        'creditsale-actual-c1',
        'creditsale-difference-c1',
        'creditsale-source',
        'creditsale-reason',
        'pinelabs-zreport-c1',
        'pinelabs-actual-c1',
        'pinelabs-difference-c1',
        'pinelabs-source',
        'pinelabs-reason',
        'other-zreport-c1',
        'other-actual-c1',
        'other-difference-c1',
        'other-source',
        'other-reason',
        'final-denomination-total',
        'cal-1-a-text-1758616033754-0',
        'cal-1-b-text-1758616033754-0',
        'cal-1-c-text-1758616033754-0',
        'cal-1-d-text-1758616033754-0',
        'cal-1-e-text-1758616033754-0',
    ];

    public static $error = 'Something went wrong! Please try again later.';

    public static function generateTaskNumber($date, $employeeId = null) {
        $index = sprintf('%02d', ChecklistTask::withTrashed()->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), date('Y-m-d', strtotime($date)))->count() + 1);

        $sequence = "WO{$index}";

        $employeeId = \App\Models\User::select('employee_id')->where('id', $employeeId)->first()->employee_id ?? '';
        if (!empty($employeeId)) {
            $sequence .= "-{$employeeId}";
        }

        $sequence .= ('-' . date('d-m-y', strtotime($date)));

        return $sequence;
    }

    public static function generateWorfklowTaskNumber() {
        $taskNo = 0;
        
        if (ChecklistTask::withTrashed()->orderBy('id', 'DESC')->first() !== null) {
            $taskNo = ChecklistTask::withTrashed()->orderBy('id', 'DESC')->first()->id;
        }

        $taskNo += 1;
        $taskNo = sprintf('%07d', $taskNo);
        $taskNo = "WF{$taskNo}";

        return $taskNo;
    }

    public static function sendPushNotification($device_ids, $data) {

        $keyFilePath = storage_path('app/firebase.json');
        
        $client = new \Google\Client();
        $client->setAuthConfig($keyFilePath);
        $client->setScopes(['https://www.googleapis.com/auth/firebase.messaging']);
    
        $tokenArray = $client->fetchAccessTokenWithAssertion();
        
        if (isset($tokenArray['error'])) {
            return false;
        }
    
        $accessToken = $tokenArray['access_token'];


        foreach ($device_ids as $did) {
            $notification = json_encode([
                "message" => [
                    "token" => $did, 
                    "notification" => [
                        "body" => $data['description'],
                        "title" => $data['title'],
                    ],
                    "android" => [
                        "priority" => "HIGH",
                    ],
                ]
            ]);
            
            $headers = array(
                'Authorization: Bearer '.$accessToken,
                'Content-Type: application/json'
            );
    
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/teapost-checklist/messages:send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $notification);
    
            curl_exec($ch);
        }

        return true;
    }

    public static function getKeyValueHavingValue($data, $prefix = '') {
        $prefix = strtolower($prefix);
        $results = [];
        
        foreach ($data as $key => $value) {
            $currentKey = $prefix ? $prefix . '.' . $key : $key;
            
            if (is_array($value) || is_object($value)) {
                $results = array_merge($results, self::getKeyValueHavingValue($value, $currentKey));
            } else {
                if (strtolower($value) === 'no' || strtolower($value) === 'fail') {
                    $results[$currentKey] = $value;
                }
            }
        }
        
        return $results;
    }

    public static function getCountHavingKey($data, $prefix = '')
    {
        $count = 0;
    
        if (is_array($data) || is_object($data)) {
            foreach ($data as $key => $value) {
                if ($key === 'name') {
                    $count++;
                }
                $count += self::getCountHavingKey($value);
            }
        }
    
        return $count;
    }    

    public static function getKeyValueHavingValueDomDashboard($data, $prefix = '') {
        $prefix = strtolower($prefix);
        $results = [];
        
        foreach ($data as $key => $value) {
            $currentKey = $prefix ? $prefix . '.' . $key : $key;
            if (is_array($value) || is_object($value)) {
                $results = array_merge($results, self::getKeyValueHavingValueDomDashboard($value, $currentKey));
            } else {
                if (strtolower($value) === 'no' || strtolower($value) === 'fail') {
                    $results[$currentKey] = $value['label'];
                }
            }
        }

        return $results;
    }

    public static function getCountHavingKeyDomDashboard($data, $prefix = '')
    {
        $count = 0;
    
        if (is_array($data) || is_object($data)) {
            foreach ($data as $key => $value) {
                if ($key === 'name') {
                    $count++;
                }
                $count += self::getCountHavingKeyDomDashboard($value);
            }
        }
    
        return $count;
    }    

    public static function slug($string, $separator = '-') {
        $string = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $string);
        $string = trim($string, $separator);
        if (function_exists('mb_strtolower')) {
            $string = mb_strtolower($string);
        } else {
            $string = strtolower($string);
        }
        $string = preg_replace("/[\/_|+ -]+/", $separator, $string);

        return $string;
    }

    public static function isBase64($string) {
        if (empty($string) || strlen($string) < 4) {
            return false;
        }
    
        $decoded = base64_decode($string, true);
        return base64_encode($decoded) === $string;
    }

    public static function getBase64Extension($base64String) {
        $matches = [];
        preg_match("/data:image\/(.*);base64/", $base64String, $matches);
        return $matches[1] ?? 'png';
    }

    public static function downloadBase64File($base64String, $title, $path)
    {        
        $extension = self::getBase64Extension($base64String);

        $fileData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64String));
        $filename = "{$title}.{$extension}";
        $filePath = "{$path}/{$filename}";

        file_put_contents($filePath, $fileData);

        return $filename;
    }

    public static function downloadBase64FileWebp($base64String, $title, $path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $base64String);
        $imageData = base64_decode($imageData);

        if ($imageData === false) {
            return false;
        }

        $image = imagecreatefromstring($imageData);
        if (!$image) {
            return false;
        }

        $filename = "{$title}.webp";
        $filePath = "{$path}/{$filename}";

        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        imagewebp($image, $filePath, 80);

        imagedestroy($image);

        return $filename;
    }

    public static function createImageThumbnail($source, $destination, $width = 200, $height = 200) {
        $tempPath = storage_path('app/public/workflow-task-uploads-thumbnails');

        if (!file_exists($tempPath)) {
            mkdir($tempPath, 0777, true);
        }

        list($sourceWidth, $sourceHeight, $sourceType) = getimagesize($source);

        switch ($sourceType) {
            case IMAGETYPE_JPEG:
                $sourceGd = imagecreatefromjpeg($source);
                break;
            case IMAGETYPE_PNG:
                $sourceGd = imagecreatefrompng($source);
                break;
            case IMAGETYPE_GIF:
                $sourceGd = imagecreatefromgif($source);
                break;
            case IMAGETYPE_WEBP:
                $sourceGd = imagecreatefromwebp($source);
                break;
            default:
                return false;
        }

        $thumb = imagecreatetruecolor($width, $height);

        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);

        imagecopyresampled($thumb, $sourceGd, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

        imagepng($thumb, $destination);

        imagedestroy($sourceGd);
        imagedestroy($thumb);

        return true;
    }

    public static function selectPointsQuestions($json)
    {
        $data = [];

        if (is_string($json)) {
            $data = json_decode($json, true);
        } else if (is_array($json) || is_object($json)) {
            $data = json_decode(json_encode($json), true);
        }

        if (!is_array($data)) {
            return [];
        }

        return array_filter($data, function ($item) {
            return isset($item['name']) && (preg_match('/^points?-/', $item['name']) || preg_match('/^point?-/', $item['name']));
        });
    }

    public static function categorizePoints($json)
    {
        $data = [];

        if (is_string($json)) {
            $data = json_decode($json, true);
        } else if (is_array($json) || is_object($json)) {
            $data = json_decode(json_encode($json), true);
        }

        if (!is_array($data)) {
            return [];
        }

        $result = [
            "positive" => [],
            "negative" => [],
            "na" => []
        ];

        foreach ($data as $item) {
            if (!isset($item['name']) || (!preg_match('/^points?-/', $item['name']) && !preg_match('/^point?-/', $item['name']))) {
                continue;
            }

            $valueLabel = isset($item['value_label']) ? strtolower($item['value_label']) : '';

            if (in_array($valueLabel, ["yes", "pass"])) {
                $result["positive"][] = $item;
            } elseif (in_array($valueLabel, ["no", "fail"])) {
                $result["negative"][] = $item;
            } elseif ($valueLabel === "na" || $valueLabel === "n/a") {
                $result["na"][] = $item;
            }
        }

        return $result;
    }

    public function taskLog($id) {
        $task = ChecklistTask::find(decrypt($id));
        $page_title = 'Task ' . $task->code . ' Log';

        return view('task-logs', compact('task', 'page_title'));
    }

    public static function getQuestionField($fields) {
        $data = 'N/A';

        try {
            if (is_array($fields)) {
                $foundYet = false;
                foreach ($fields as $row) {
                    if (strpos($row->name, 'checkbox-group') !== false || strpos($row->name, 'radio-group') !== false) {
                        $data = $row->label;
                        $foundYet = true;
                        break;
                    }
                }

                if ($foundYet === false) {
                    $data = isset($fields[0]->label) ? $fields[0]->label : $data;
                }
            }
        } catch (\Exception $e) {
            $data = isset($fields[0]->label) ? $fields[0]->label : $data;
        }

        return $data;
    }

    public static function isPointChecklist($json)
    {
        $data = $res = [];

        if (is_string($json)) {
            $data = json_decode($json, true);
        } else if (is_array($json) || is_object($json)) {
            $data = json_decode(json_encode($json), true);
        }

        if (!is_array($data)) {
            $res = $data = [];
        }

        $res = array_filter($data, function ($innerItem) {
            return array_filter($innerItem, function ($item) {
                return isset($item['name']) && (preg_match('/^points?-/', $item['name']) || preg_match('/^point?-/', $item['name']));
            });
        });

        return boolval($res);
    }

    public static function getCountOfCountableQuestions($json)
    {
        $data = [];

        if (is_string($json)) {
            $data = json_decode($json, true);
        } else if (is_array($json) || is_object($json)) {
            $data = json_decode(json_encode($json), true);
        }

        if (!is_array($data)) {
            return [];
        }

        return array_filter($data, function ($item) {
            return isset($item['name']) && (preg_match('/^radio?-/', $item['name']) || preg_match('/^select?-/', $item['name']));
        });
    }

    public static function getBooleanFields($json) {
        $data = [];

        if (is_string($json)) {
            $data = json_decode($json, true);
        } else if (is_array($json) || is_object($json)) {
            $data = json_decode(json_encode($json), true);
        }

        if (!is_array($data)) {
            return [];
        }

        $result = [
            "truthy" => [],
            "falsy" => []
        ];

        foreach ($data as $item) {
            if (!(isset($item['name']) && (preg_match('/group-/', $item['name'])) || preg_match('/radio-/', $item['name']))) {
                continue;
            }

            $valueLabel = isset($item['value_label']) ? strtolower($item['value_label']) : '';

            if (in_array($valueLabel, ["yes", "pass"])) {
                $result["truthy"][] = $item;
            } elseif (in_array($valueLabel, ["no", "fail"])) {
                $result["falsy"][] = $item;
            }
        }

        return $result;
    }

    public static function addGraceTime($timestamp, $time) {
        list($hours, $minutes, $seconds) = explode(':', $time);
        $carbonDate = Carbon::createFromFormat('d-m-Y H:i:s', $timestamp);
        $carbonDate->addHours($hours)->addMinutes($minutes)->addSeconds($seconds);

        return $carbonDate->toDateTimeString();
    }

    public static function getFirstBranch($user, $branchType = null) {
        $branch = Designation::select('type_id')
        ->where('type', $branchType)
        ->where('user_id', $user)
        ->first();

        if ($branch) {
            return [
                'branch_type' => $branchType,
                'branch_id' => $branch->type_id ?? null,
                'user_id' => $user
            ];
        } else {
            return [
                'branch_type' => 1,
                'branch_id' => null,
                'user_id' => $user
            ];
        }
    }

    public static function calculateTotalTime($taskId)
    {
        $submissionTimes = SubmissionTime::where('task_id', $taskId)
            ->orderBy('timestamp', 'asc')
            ->get();
        
        $totalSeconds = 0;
        $startTime = null;
        
        foreach ($submissionTimes as $submission) {
            if ($submission->type == 1) {
                $startTime = Carbon::parse($submission->timestamp);
            } elseif ($submission->type == 2 && $startTime) {
                $endTime = Carbon::parse($submission->timestamp);
                $diffInSeconds = $endTime->diffInSeconds($startTime);
                $totalSeconds += $diffInSeconds;
                $startTime = null;
            }
        }
        
        if ($startTime !== null) {
            $now = Carbon::now();
            $diffInSeconds = $now->diffInSeconds($startTime);
            $totalSeconds += $diffInSeconds;
        }
        
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;
        
        $formattedTime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        
        return $formattedTime;
    }

    public static function calculateRemainingTime($totalAllocated, $timeSpent = null, $allowNegative = true)
    {        
        $totalAllocatedParts = explode(':', $totalAllocated);
        $totalAllocatedSeconds = ($totalAllocatedParts[0] * 3600) + ($totalAllocatedParts[1] * 60) + $totalAllocatedParts[2];
        
        $timeSpentParts = explode(':', $timeSpent);
        $timeSpentSeconds = ($timeSpentParts[0] * 3600) + ($timeSpentParts[1] * 60) + $timeSpentParts[2];
        
        $remainingSeconds = $totalAllocatedSeconds - $timeSpentSeconds;
        
        $isNegative = false;
        if (!$allowNegative && $remainingSeconds < 0) {
            $remainingSeconds = 0;
        } elseif ($remainingSeconds < 0) {
            $isNegative = true;
            $remainingSeconds = abs($remainingSeconds);
        }
        
        $hours = floor($remainingSeconds / 3600);
        $minutes = floor(($remainingSeconds % 3600) / 60);
        $seconds = $remainingSeconds % 60;
        
        $formattedTime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        
        if ($isNegative) {
            $formattedTime = '-' . $formattedTime;
        }
        
        return $formattedTime;
    }

    public static function setting($key = null)
    {
        if($key){
            try{
                return \App\Models\TicketSetting::select($key)->first()->$key;
            } catch (\Exception $e) {
            }
        }
		$settingData = \App\Models\TicketSetting::first();
		if (!$settingData) {
			$settingData = (object) array('name' => '', 'favicon' => '', 'logo' => '');
		}
		return $settingData;
	}

        public static function ticket_mail_send($id,$ticket_type){
        $ticket = \App\Models\Ticket::with(['user','latest_comments'])->find($id);
        $agents = TicketMember::where('ticket_id', $ticket->id)->pluck('user_id')->toArray();
        $users = [];

        if($ticket_type == 'Add' && !empty($agents)){
            $users = \App\Models\User::whereNotIn('id',[auth()->user()->id])->whereIn('id',$agents)->get();
        }
        else if($ticket_type == 'Reply' && !empty($agents)){
            $users = \App\Models\User::whereIn('id',$agents)->get();
        }
        else if($ticket_type == 'Complete'){
            $users = [$ticket->user];
        } else if($ticket_type == 'Estimate date added'){
            $users = [$ticket->user];
        } else if($ticket_type == 'Estimate date changed'){
            $users = [$ticket->user];
        } else if($ticket_type == 'Reopened' && !empty($agents)){
            $users = \App\Models\User::whereIn('id',$agents)->get();
        }

        if(!empty($users)){
            $content = $ticket->content;

            if($ticket_type == 'Estimate date added' || $ticket_type == 'Estimate date changed'){
                $content = 'Estimated date is '.date('d-m-Y',strtotime($ticket->estimate_time));
            }

            $allUsers = $users;

            foreach ($allUsers as $users) {
                $ticket_data = array(
                    'the_ticket' => $ticket,
                    'ticket_type' => $ticket_type,
                    'ticket_added_by' => auth()->user()->email,
                    'ticket_number' => $ticket->ticket_number,
                    'subject' => $ticket->subject,
                    'content' => $content,
                    'ticket_replay_message' => isset($ticket->latest_comments->html) && $ticket->latest_comments->html != null ? $ticket->latest_comments->html : ''
                );

                TicketMail::dispatch($ticket_data, $users, $ticket_type, $ticket);
            }
        }
    }

    public static function check_ticket_replay_by_agent($id){

        if(isset(auth()->user()->roles[0]->id)){
            $status = true;
        } else {
            $ticket_comment = \App\Models\Comment::where(['ticket_id' => $id])->count();
            $status = $ticket_comment > 0 ? true : false;
        }

        return $status;
    }

    public static function getLatestStatus ($taskId, $className) {
        return \App\Models\Ticket::select('status_id')->where('task_id', $taskId)->where('field_id', $className)->first()->status->name ?? 'Pending';
    }

    public static function parseFlexibleDate($dateString) {
        $formats = ['d/m/Y', 'd-m-Y'];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            $errors = \DateTime::getLastErrors();

            if ($errors === false) {
                return $date->format('Y-m-d');
            }

            if ($date && $errors['warning_count'] == 0 && $errors['error_count'] == 0) {
                return $date->format('Y-m-d');
            }
        }

        return '1970-01-01';
    }


    public static function getObjectsByName($jsonArray, $name)
    {
        $result = array_filter($jsonArray, function ($item) use ($name) {
            return isset($item->name) && $item->name === $name;
        });

        $result = array_values($result);

        if (isset($result[0]->value)) {
            if (is_string($result[0]->value)) {
                return $result[0]->value;
            }
        }

        return "";
    }

    public static function normalizeJson($json)
    {
        if (is_string($json)) {
            $decoded = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }

            return $json;
        }

        if (is_object($json)) {
            return json_decode(json_encode($json), true);
        }

        if (is_array($json)) {
            return $json;
        }

        return $json;
    }
    public static function getTotalQuestionsCount($json)
    {
        $fields = [
            'radio-group' => 'radio-group',
            'file' => 'file',
            'checkbox-group' => 'checkbox-group',
            'number' => 'number',
            'text' => 'text',
            'textarea' => 'textarea',
            'autocomplete' => 'autocomplete',
            'date' => 'date',
            'select' => 'select',
            'signature' => 'signature'
        ];

        $json = self::normalizeJson($json);
        $count = 0;

        foreach ($json as $page) {
            foreach ($page as $object) {
                if (isset($object->type) && isset($fields[$object->type])) {
                    $count++;
                }
            }
        }

        return $count;
    }

    public static function getFormVersion($checklistId = null, $oldHash = null)
    {
        if (!$checklistId) {
            return null;
        }

        $theChecklist = \App\Models\DynamicForm::where('id', $checklistId)->withTrashed()->first();

        if (!isset($theChecklist->id)) {
            return null;
        }

        $formVersionId = \App\Models\FormVersion::where('checklist_id', $checklistId)
            ->where('hash', $oldHash)
            ->latest('created_at')
            ->value('id');

        if ($formVersionId) {
            return $formVersionId;
        }

        $hash = md5(json_encode($theChecklist->schema));

        $dynamic = \App\Models\FormVersion::updateOrCreate([
            'checklist_id' => $checklistId,
            'hash' => $hash
        ],[
            'checklist_id' => $checklistId,
            'added_by' => auth()->check() ? auth()->user()->id : null,
            'form' => $theChecklist->schema,
            'hash' => $hash
        ]);

        return $dynamic->id;
    }

    public static function getVersionForm($versionId = null) {
        if ($versionId) {
            return \App\Models\FormVersion::find($versionId)->form ?? [];
        } else {
            return [];
        }
    }
}
