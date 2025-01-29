<?php

namespace App\Services;

use App\Enums\Frequency;
use App\Enums\PaymentStatus;
use App\Jobs\QueueMail;
use App\Models\AjoCache;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Str;
use Twilio\Rest\Client;

class Util
{
    /**
     * Upload a file to S3 and return its URL.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return string
     */
    public static function upload($file,$key, $dir = "public", $useFileName = false)
    {
        try {
            $filePath = Storage::put($dir ."/".$key. "/" . date('Y') . "/" . date('M'), $file);
            //$filePath = Storage::disk('s3')->put($dir ."/".$key. "/" . date('Y') . "/" . date('M'), $file);

            if ($filePath) {
                return $filePath;
            } else {
                Log::info("Failed to upload the file.");
            }
        } catch (\Exception $e) {
            Log::error('Error uploading file to S3: ' . $e->getMessage());
            return '';
        }
    }

    public static function uploader($file, $key='storage'){
        return $file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile ? self::upload($file, $key, 'private') : $file;
    }

    /**
     * Generate a pre-signed URL for a file on S3.
     *
     * @param string $key
     * @param int $minutes
     * @return string
     */
    public static function publicUrl($relativePath, $minutes = 60)
    {
        try {
            return config('app.url') . str_replace('public','storage', $relativePath);
            // Ensure the relative path does not start with a leading slash
            $key = ltrim($relativePath, '/');
    
            // Log the key to ensure it's correct
            
    
            // Get the S3 client directly
            $client = Storage::disk('s3')->getClient();
            //$client = Storage::disk('s3')->getClient();
            $expiry = "+{$minutes} minutes";
    
            // Prepare the GetObject command
            $command = $client->getCommand('GetObject', [
                'Bucket' => config('default.aws.bucket') ,
                'Key' => $key,
            ]);
    
            // Create a presigned request
            $request = $client->createPresignedRequest($command, $expiry);
            
            // Return the presigned URL
            return (string) $request->getUri();
        } catch (\Exception $e) {
            // Log the exception message
             Log::error('Error generating presigned URL: ' . $e->getMessage());
            return "";
        }
    }
    
    /**
     * Upload a file from a request and return the updated request data with the file URL.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $fileName
     * @return array
     */
    public static function getRequestUploadedData(Request $request, $fileName = 'value')
    {
        $data = $request->all();
        $fileNames = is_array($fileName) ? $fileName : [$fileName];        
        
        foreach ($fileNames as $name) {
            if ($request->hasFile($name)) {
                $file = $request->file($name);
               // Log::info('Uploading file:', ['name' => $file->getClientOriginalName(), 'mime' => $file->getClientMimeType()]);
                
                $pictureUrl = self::upload($file, $name);                
                if (empty($pictureUrl)) {
                    Log::info("Failed to upload the file or generate the URL.");
                }
    
                $data[$name] = $pictureUrl;
            }
        }
    
        foreach ($data as $key => &$value) {
            if ($value == 'null') {
                $data[$key] = null;
            }
        }
    
        return $data;
    }
    
    public static function getUploadPath($url) {
        // Parse the URL to get its components
        $parsedUrl = parse_url($url);
    
        // Check if the path component exists
        if (isset($parsedUrl['path'])) {
            // Remove the leading slash from the path
            $key = ltrim($parsedUrl['path'], '/');
            
            // Check if the key contains query parameters and remove them
            if (isset($parsedUrl['query'])) {
                $key = explode('?', $key)[0];
            }
    
            return $key;
        }
        return null;
    }

    public static function verifyOtp($request, $user_type){
        $user = null;
        $user =  User::where('email', $request->email)->where('otp', $request->otp)->where('otp_expires_at', '>', now())->first();                    

        if($user){
            $user->update(['email_verified_at' => now(),'otp' => null,'otp_expires_at' => null]);            
        }else{
            abort(422,"Invalid or expired OTP");
        }        
        return $user;
    }

    public static function verifyPhoneNumber($request)
    {
        $user = $request->user();

        if (is_null($user)) {
            abort(401, "Unauthorized");
        }

        $user = User::where('id', $user->id)
                    ->where('phone_number_otp', $request->otp)
                    ->where('phone_number_otp_expires_at', '>',Carbon::now()->format("Y-m-d H:i:s"))
                    ->first();

        if ($user) {
            $user->update([
                'phone_number_verified_at' => now(),
                'is_verified_phone_number' => 1,
                'phone_number_otp' => null,
                'phone_number_otp_expires_at' => null
            ]);

            return true;
        }

        return false;
    }


    static public function getTimeAt($dateTime)
    {        
        $created_at = Carbon::parse($dateTime);
        // Get the current time as a Carbon instance
        $current_time = Carbon::now();
        
        // Calculate the time difference between now and the 'created_at' timestamp
        $diff_seconds = floor($created_at->diffInSeconds($current_time));
        $diff_minutes = floor($created_at->diffInMinutes($current_time));
        $diff_hours = floor($created_at->diffInHours($current_time));
        
        // Format the output based on the time difference
        if ($diff_seconds < 60) {
            $time_ago = "$diff_seconds second" . ($diff_seconds > 1 ? 's' : '') . " ago";
        } elseif ($diff_minutes < 60) {
            $time_ago = "$diff_minutes minute" . ($diff_minutes > 1 ? 's' : '') . " ago";
        } elseif ($diff_hours < 12) {
            $time_ago = "$diff_hours hour" . ($diff_hours > 1 ? 's' : '') . " ago";
        } else {
            // If more than a day ago, show the time in '2:30 PM' format
            $time_ago = $created_at->format('g:i A');
        }
        
        return $time_ago;   
    }

    static public function withStrictModeOff(callable $callback)
    {
        try {
            // Turn off strict mode
            DB::statement('SET SESSION sql_mode = ""');

            // Execute the callback
            $result = $callback();
            // Turn on strict mode
            DB::statement('SET SESSION sql_mode = "STRICT_TRANS_TABLES,STRICT_ALL_TABLES"');

            return $result;
        } catch (\Exception $e) {
            // Handle any exceptions that occur during execution
            DB::statement('SET SESSION sql_mode = "STRICT_TRANS_TABLES,STRICT_ALL_TABLES"');
            throw $e;
        }
    }

    static public function reduceImageSize($imagePath){
        // Increase execution time limit
        set_time_limit(120); // Increase the limit to 120 seconds
      //  Log::info('image test --->'.$imagePath);
        list($width, $height) = getimagesize($imagePath);
    
        // Handle different image types
        $imageType = exif_imagetype($imagePath);
        if ($imageType == IMAGETYPE_JPEG) {
            $image = imagecreatefromjpeg($imagePath);
        } elseif ($imageType == IMAGETYPE_PNG) {
            $image = @imagecreatefrompng($imagePath);
        } elseif ($imageType == IMAGETYPE_GIF) {
            $image = imagecreatefromgif($imagePath);
        } else {
            throw new \Exception('Unsupported image type.');
        }
    
        // Calculate new dimensions
        $newWidth = 1172; // Desired width
        $newHeight = ($height / $width) * $newWidth;
    
        // Create a new empty image with the new dimensions
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
    
        // Copy the original image into the resized image
        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
        // Start output buffering to capture the image data
        ob_start();
        imagejpeg($resizedImage, null, 100);
        $imageData = ob_get_contents();
        ob_end_clean();
    
        // Free up memory
        imagedestroy($image);
        imagedestroy($resizedImage);
    
        // Return the image as a base64-encoded string
        return 'data:image/jpeg;base64,' . base64_encode($imageData);
    }
    

    static public function throwError(\Exception $e){
        if(config('app.env') == 'local'){
          return $e;       
        }
        return response()->json('something went wrong', 400);
    }

    static public function GenericUtils($emailOrPhone, $expired_at, $code,$user_id=null,$user_type=null, $type='verify'){
        $uniqueToken = Str::random(40);
        return AjoCache::updateOrCreate([
            'email'=>$emailOrPhone
        ],[
            'token'=> $uniqueToken,
            'expires_at'=> $expired_at,
            'data'=>  $code,
            'user_type'=>$user_type,
            'type'=>$type,
            'user_id'=>$user_id
        ]);
    }
    
    static public function OTPUtils($user, $expired_at, $code, $type='verify'){

        $uniqueToken = Str::random(40);
        AjoCache::updateOrCreate([
            'email'=>$user->email
        ],[
            'token'=> $uniqueToken,
            'expires_at'=> $expired_at,
            'data'=>  $code,
            'user_type'=>'user',
            'type'=>$type,
            'user_id'=>$user->id
        ]);
       // Cache::put($uniqueToken, [$user->email,$user_type], now()->addMinutes(10));

        $data = [
            "otp" => $code,
            "expires_at" => $expired_at,
            "email" => $user->email,
            "token"=>$uniqueToken,
            "user_type" =>'user',
            "name" => $user->business_name ?? $user->first_name . ' ' . $user->middle_name . ' ' . $user->last_name
        ];

        return $data;
    }

    static public function imageUrlToBase64($url)
    {
        try{

            $image = file_get_contents($url);
            if ($image !== false){
                return 'data:image/jpg;base64,'.base64_encode($image);
                
            }
        }catch(\Exception $e){
            Log::error("Failed to open stream: HTTP request failed! HTTP/1.1 404 Not Foun: Util::imageUrlToBase64");
        }
    }

    static public function invoiceInitiated($invoice){
        $link = config('default.portal.domain') . '/' . 'verify/' . $invoice->invoice_number;
        $taxable = $invoice->taxable;
       
        $invoiceMailData = $taxable->toArray();
        $invoiceMailData['email'] = $invoiceMailData['email'] ?? config('default.email');
        $invoiceMailData['link'] = $link;
        $invoiceMailData['message'] = "
                <p>
                    A payment of {$invoice->amount} has been initiated on your account with invoice number ({$invoice->invoice_number}).
                </p>
                <p>
                    Kindly use the link below to proceed with payment:
                </p>
                <a href='$link'>Click to Continue</a>
            ";

        QueueMail::dispatch($invoiceMailData, 'invoice_initiated', "GIRS Invoice Created");

    }

    public static function sendSMS($to, $message, $type = "single")
    {
        $twilioSid = config('default.twilio_account_sid');
        $twilioToken = config('default.twilio_auth_token');
        $twilioMessageSid = config('default.twilio_message_sid');
    
        if (empty($twilioSid) || empty($twilioToken) || empty($twilioMessageSid)) {
            Log::error('Twilio configuration is incomplete.');
            return;
        }
    
        try {
            $client = new Client($twilioSid, $twilioToken);
    
            if ($type === "bulk") {
                if (!is_array($to)) {
                    Log::error('For bulk SMS, the recipient list must be an array.');
                    return;
                }
    
                foreach ($to as $recipient) {
                    $response = $client->messages->create(
                        $recipient,
                        [
                            'messagingServiceSid' => $twilioMessageSid,
                            'body' => $message,
                        ]
                    );
    
                    Log::info('Bulk SMS sent successfully to ' . $recipient . ': ' . $response->sid);
                }
            } else {
                $response = $client->messages->create(
                    $to,
                    [
                        'messagingServiceSid' => $twilioMessageSid,
                        'body' => $message,
                    ]
                );
    
                Log::info('Single SMS sent successfully: ' . $response->sid);
            }
        } catch (\Exception $e) {
            Log::error('SMS sending exception: ' . $e->getMessage());
        }
    }
    
   
    public static function generateReferenceCode()
    {
        // Prefix from configuration
        $prefix = config('default.prefix');
        
        // Get the current date in Ymd format (e.g., 20250105)
        $datePart = now()->format('Ymd');
        
        // Generate a random string of 5 characters using Str::random
        $randomPart = Str::random(5); // Random string of length 5
        
        // Combine all parts: Prefix + Date + Random string
        $referenceCode = $prefix . $datePart . strtoupper($randomPart);
        
        // Ensure the reference code is exactly 15 characters long (if not, truncate or adjust)
        return substr($referenceCode, 0, 15);
    }

    public static function pendingContribution($ajo, $ajoMember) {
        $lastComplianceDate = $ajoMember->last_date_of_compliance ?? $ajo->start_date;
        $frequency = $ajo->frequency;
        $now = Carbon::parse($ajo->end_date);
    
        // Calculate pending contribution periods
        $pendingPeriods = match (intval($frequency)) {
            Frequency::DAILY => intval(Carbon::parse($lastComplianceDate)->diffInDays($now)),
            Frequency::WEEKLY => intval(Carbon::parse($lastComplianceDate)->diffInWeeks($now)),
            Frequency::MONTHLY => intval(Carbon::parse($lastComplianceDate)->diffInMonths($now)),
            Frequency::YEARLY => intval(Carbon::parse($lastComplianceDate)->diffInYears($now)),
            default => 0,
        };

        $period = match (intval($frequency)) {
            Frequency::DAILY => 'days',
            Frequency::WEEKLY => 'weeks',
            Frequency::MONTHLY => 'months',
            Frequency::YEARLY => 'years',
            default => 0,
        };
           
    
        if ($pendingPeriods <= 0) {
            return[ 
              'period'=> "No pending contributions.",
              'pending' => 0,
            ];
        }

        return [
           'period'=> $pendingPeriods . " " . $period,
           'pending' => $pendingPeriods,
        ];
    
    }

    public static function makeContribution($ajo, $ajoMember) {
        $lastComplianceDate = $ajoMember->last_date_of_compliance ?? $ajo->start_date;
        $frequency = $ajo->frequency;
        $now = Carbon::now();
    
        // Calculate pending contribution periods
        $pendingPeriods = match (intval($frequency)) {
            Frequency::DAILY => intval(Carbon::parse($lastComplianceDate)->diffInDays($now)),
            Frequency::WEEKLY => intval(Carbon::parse($lastComplianceDate)->diffInWeeks($now)),
            Frequency::MONTHLY => intval(Carbon::parse($lastComplianceDate)->diffInMonths($now)),
            Frequency::YEARLY => intval(Carbon::parse($lastComplianceDate)->diffInYears($now)),
            default => 0,
        };

        if ($pendingPeriods <= 0) {
            return "No pending contributions.";
        }
    
        $user = $ajoMember->user;
        $userWallet = $user->getWallet($ajo->currency);
        $ajoWallet = $ajo->getWallet($ajo->currency);
    
        if (!$userWallet || !$ajoWallet) {
            return "Currency wallet not found for either user or Ajo.";
        }
    
        // Calculate pending amount
        $totalPendingAmount = $pendingPeriods * $ajo->amount;
    
        if ($userWallet->balance < $ajo->amount) {
            return "Insufficient funds for even one contribution.";
        }
    
        // Calculate max contributions based on available balance
        $maxPeriodsAffordable = floor($userWallet->balance / $ajo->amount);
        $actualContributionPeriods = min($maxPeriodsAffordable, $pendingPeriods);
    
        // Calculate the actual amount to contribute
        $actualAmount = $actualContributionPeriods * $ajo->amount;
    
        // Update last compliance date based on actual contributions
        $lastComplianceDate = Carbon::parse($lastComplianceDate);
        $lastExpectedDate = match (intval($frequency))  {
            Frequency::DAILY => $lastComplianceDate->addDays($actualContributionPeriods),
            Frequency::WEEKLY => $lastComplianceDate->addWeeks($actualContributionPeriods),
            Frequency::MONTHLY => $lastComplianceDate->addMonths($actualContributionPeriods),
            Frequency::YEARLY => $lastComplianceDate->addYears($actualContributionPeriods),
            default => $lastComplianceDate,
        };
        
        DB::beginTransaction();
        try {
            // Debit the user's wallet  
            $userWallet->withdraw($actualAmount);
    
            // Credit the Ajo's wallet
            $ajoWallet->deposit($actualAmount, [
                'note' => 'Payment received',
                'payee_type' => get_class($userWallet),
                'payee_id' => $userWallet->id,
            ]);
            $ajoWallet->total_collection = $ajoWallet->total_collection + $actualAmount;
            $ajoWallet->save();
            // Update last compliance date
            $ajoMember->last_date_of_compliance = $lastExpectedDate->format('Y-m-d');
            $ajoMember->contributed += $actualAmount;
            $ajoMember->save();
    
            DB::commit();
    
            return "Contribution successful. {$actualAmount} credited to Ajo wallet for {$actualContributionPeriods} period(s).";
        } catch (\Exception $e) {
            DB::rollBack();
            //abort(400, "Contribution failed: " . $e->getMessage());
            return "Contribution failed: " . $e->getMessage();
        }
    }
    

}