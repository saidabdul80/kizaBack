<?php

namespace App\Http\Controllers;

use App\Events\AccountCreated;
use App\Events\EmailVerified;
use App\Jobs\QueueMail;
//use App\Jobs\SendSMS;
use App\Models\PaymentGateway;
use App\Models\State;
use App\Models\AjoCache;
use App\Models\Vendor;
use App\Services\Util;
use Carbon\Carbon;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Exports\ReportsExport;
use App\Http\Resources\WalletTransactionResource;
use App\Mail\SendMail;
use App\Mail\SendMailNoQueue;
use App\Models\Ajo;
use App\Models\AjoMember;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
class CentralController extends Controller
{
    public function getPresignedUrl(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
        ]);
        $key = $request->input('key');
        return response()->json(Util::publicUrl($key));
    }

  
    

    // public function verifyIdentity($token)
    // {
    //     $response = AjoCache::where('token', $token)->first();
    //     if (is_null($response)) {
    //         abort(404, 'Token not found');
    //     }

    //     $userId = $response['user_id'];
    //     $userType = $response['user_type'];
    //     $verificationType = $response['type'];
    //     $cleanJsonString = str_replace(['\\', "\n", "\r"], '', $response['data']); 
    //     $response = json_decode($cleanJsonString);

    //     switch ($userType) {
    //         case 'vendor':
    //             $user = Vendor::find($userId);
    //             !is_null($user->is_verified_rc_number) ? abort(400, 'RC/BN already verified'): null;
    //             $app = app(VendorController::class);
    //             break;
    //         case 'individualTaxPayer':
    //             $user = IndividualTaxPayer::find($userId);
    //             $user->nin_is_verified==1 ? abort(400, 'NIN already verified'): null;
    //             $app = app(IndividualTaxPayerController::class);
    //             break;
    //         case 'corporateTaxPayer':
    //             $user = CorporateTaxPayer::find($userId);
    //             !is_null($user->is_verified_rc_number) ? abort(400, 'RC/BN already verified'): null;
    //             $app = app(CooporateTaxPayerController::class);
    //             break;
    //         case 'corporate':
    //             $user = CorporateTaxPayer::find($userId);
    //             $user->tin_is_verified==1 ? abort(400, 'TIN already verified'): null;
    //             $app = app(CooporateTaxPayerController::class);
    //             break;
    //         case 'individual':
    //             $user = IndividualTaxPayer::find($userId);
    //             $user->tin_is_verified==1 ? abort(400, 'TIN already verified'): null;
    //             $app = app(IndividualTaxPayerController::class);
    //             break;
    //         default:
    //             break;
    //     }

    //     if ($response) {
    //         if ($verificationType == 'tin') {
    //             $app->updateRecordWithTinData($user, $response);
    //             $message = 'TIN verified Successfully';
    //         } else {
    //             $app->updateRecordWithIdentityInfo($user, $response);
    //             $message = 'Identity verified Successfully';
    //         }
    //         return response()->json($message);
    //     }

    // }
    public function verifyIdentity($token)
    {
        $cacheResponse = AjoCache::where('token', $token)->firstOrFail();

        $userId = $cacheResponse['user_id'];

        $verificationType = $cacheResponse['type'];

        $responseData = json_decode(str_replace(['\\', "\n", "\r"], '', $cacheResponse['data']));

        if (is_null($responseData)) {
            abort(400, 'Invalid JSON response');
        }

        $userTypeMappings = [User::class, 'is_verified_email', UserController::class, 'Email already verified'];

        [$model, $verificationField, $controller, $errorMessage] = $userTypeMappings;

        $user = $model::find($userId);

        if ($user->$verificationField) {
            abort(400, $errorMessage);
        }

        $app = app($controller);

        if ($verificationType == 'tin') 
        { 
            $app->updateRecordWithTinData($user, $responseData); 
            $message = 'TIN verified successfully'; 
        }
         else { 
            $app->updateRecordWithIdentityInfo($user, $responseData);
            $message = 'Identity verified successfully'; 
        }

        return response()->json(['message' => $message], 200);
    }

    
    public function confirmIdentityVerification(Request $request)
    {
        $request->validate([
            "searchParameter" => "required",
            "type" => "required|in:dob,otp",
            "value" => "required",
        ]);
        $type = $request->type;
        $value = $type == 'dob'?Carbon::parse($request->value)->format('Y-m-d'):$request->value;
        
        $user = $this->getCachedIdentityInfo($request->searchParameter,$type,$value);
        if(empty($user)){
            return response()->json('Invalid OTP');
        }
        $data = $user->data;
        if($user->type == 'bvn'){
           $response = $this->generateTin($data);
           $data->tin = $response['tin'];
        }
        $user->delete();
        return response()->json($this->restructureData((array) json_decode($data)));
    }

    private function restructureData(array $thirdPartyData)
    {
        $mapping = [
            'first_name' => 'firstname',
            'middle_name' => 'middlename',
            'last_name' => 'surname',
            'gender' => 'gender',
            'email' => 'email',
            'phone_number' => 'telephoneno',
        ];

        $mappedData = [];
        foreach ($mapping as $modelField => $thirdPartyField) {
            if (isset($thirdPartyData[$thirdPartyField])) {
                $mappedData[$modelField] = $thirdPartyData[$thirdPartyField];
            }
        }

        if(isset($thirdPartyData['companyEmail'])){
            $mappedData['email']  = $thirdPartyData['companyEmail'];
        }

        return $mappedData;
    }
   

    public function initiateIdentityVerification(Request $request)
    {
        $request->validate([
            'searchParameter' => 'required|string',
            'verificationType' => 'required|string|in:nin,bvn,tin,rc_number,NIN,BVN,TIN,RC_Number,RC_NUMBER,phone_number',
            'userType' => 'nullable|string|in:individual,corporate,INDIVIDUAL,CORPORATE',
            'code'=>'required'//in:0,1
        ]);

        $user = $request->user();
        $token = $request->input('searchParameter');
        $verificationType = strtolower($request->input('verificationType'));
        $userType = strtolower($request->input('userType'));
        $code = $request->code;
        // Ensure the user's identity is not already verified
        if ($this->isUserAlreadyVerified($user, $verificationType)) {
            return response()->json(['message' => $this->getAlreadyVerifiedMessage($verificationType)], 400);
        }

        if($verificationType == 'phone_number'){
            $verificationUrl = route('verify_identity', ['token' => $token]);
            $util = Util::GenericUtils($token, expires_at(30),mt_rand(100000,999999),$user?->id, $user?->user_type,'verify_phone_number');
            // Util::sendSMS($util->email,"
            //     Click the link below to complete your {$verificationType} verification or use the code: $verificationUrl
            // ");
        }else{
            
            $response = $this->fetchAndCacheIdentityInfo($user, $userType, $token, $verificationType, $code);
      
            if ($response) {
                $this->sendVerificationNotifications($response, $token, $verificationType,  $code);
                return response()->json(['message' => 'Verification link has been sent to you.']);
            }
        }

        return response()->json(['message' => 'Invalid Identity Number'], 422);
    }

    private function isUserAlreadyVerified($user, $verificationType)
    {
        switch ($verificationType) {
            case 'nin':
                return $user?->nin_is_verified;
            case 'tin':
                return $user?->tin_is_verified;
            case 'rc_number':
                return !is_null($user?->rc_number_is_verified);
            default:
                return false;
        }
    }

    private function getAlreadyVerifiedMessage($verificationType)
    {
        switch ($verificationType) {
            case 'nin':
                return 'NIN already verified';
            case 'tin':
                return 'TIN already verified';
            case 'rc_number':
                return 'RC/BN already verified';
            default:
                return 'Identity already verified';
        }
    }

    private function getCachedIdentityInfo($token, $col = 'code', $value= null)
    {
        return AjoCache::where('token', $token)->where($col,$value)->first();
    }

    private function fetchAndCacheIdentityInfo($user, $userType, $token, $verificationType, $code)
    {
        $response = $this->getIdentityInfo($token, $verificationType, $userType);
   
        if ($response) {
            $keys = array_keys($response);
            
            $phone_number_key = array_values(preg_grep('/phone/i', $keys));
            $dob_key = array_values(preg_grep('/birth/i', $keys));

            $dob = null;
            $phone_number = null;
            if ($phone_number_key) {
                $phone_number_key = $phone_number_key[0]; 
                $phone_number = $response[$phone_number_key];
            } 

            if($dob_key){
                $dob_key = $dob_key[0];
                $dob = $response[$dob_key];
            }
            
            $user_type = null;
            switch ($verificationType) {
                case 'tin':
                    $user_type = $userType;
                    break;
                default:
                    $user_type = $user?->user_type;
                    break;
            }
            
           $response = AjoCache::updateOrCreate(['token' => $token],[
                'user_id' => $user?->id,
                'user_type' => $user_type,
                'email' => $verificationType == 'rc_number' ? $response['companyEmail'] :$response['email'],
                'phone_number'=> validate_phone_number($phone_number),
                'otp' => $code=='1'? mt_rand(100000, 999999):null, 
                'dob' => $dob,
                'type'=> strtolower($verificationType),
                'data' => json_encode($response),
            ]);
        }
        return $response;
    }

    private function sendVerificationNotifications($user, $token, $verificationType,$code)
    {
        $verificationUrl = route('verify_identity', ['token' => $token]);

        if ($user instanceof \Illuminate\Database\Eloquent\Model) {
            $user = $user->toArray();
        }
       
        if ($user['email'] !== null) {
            QueueMail::dispatch(
                array_merge($user, ['url' => $verificationUrl]),
                'identity_verification',
                ucfirst($verificationType) . ' Verification'
            );
        }
        
    }

    private function getJtbToken()
    {
        // Send a request to obtain a token from JTB's API
        $response = Http::post(config('default.jtb.base_url')."/GetTokenID", [
            'email' => config('default.jtb.user'),
            'password' => config('default.jtb.pass'),
            'clientname' => 'jtb'
        ]);

        $data = $response->json();
        return $data['tokenId'] ?? null;
    }

    private function getIdentityInfo($searchParameter, $verificationType, $userType)
    {
        $cleanedVariable = preg_replace('/[\x{202A}-\x{202E}]/u', '', $searchParameter);
        switch (strtolower($verificationType)) {
            case 'rc_number':
                $base_url = config('default.seamfix.base_url');
                $payload =  [
                    "searchParameter" => $cleanedVariable,
                    "verificationType" => "NG-CAC-PREMIUM-VERIFICATION",
                    "countryCode" => "NG",
                ];
                $headers = [
                    "apiKey" =>  config('default.seamfix.cac_api_key'),
                    "userid" => config('default.seamfix.user_id')
                ];
                break;
            case 'nin':
                $base_url = config('default.seamfix.base_url');
                $payload =  [
                    "searchParameter" => $cleanedVariable,
                    "verificationType" => "NIN-VERIFY",
                    "countryCode" => "NG",
                ];
                $headers = [
                    "apiKey" =>  config("default.seamfix.nin_api_key"),
                    "userid" => config('default.seamfix.user_id')
                ];
                break;
            case 'bvn':
                $base_url = config('default.seamfix.base_url');
                $payload =  [
                    "searchParameter" => $cleanedVariable,
                    "verificationType" => "BVN-FULL-DETAILS",
                    "countryCode" => "NG",
                ];
                $headers = [
                    "apiKey" =>  config("default.seamfix.bvn_api_key"),
                    "userid" => config('default.seamfix.user_id')
                ];
                break;
            case 'tin':
                $apiKey  = $this->getJtbToken();
                $type = $userType == 'individual' ? '/individualtinvalidation?' : '/nonindividualtinvalidation?';
                $base_url = config('default.jtb.base_url').$type.'tokenid='.$apiKey;
                //$verificationType2 = "TIN";
                $payload =  [
                    "tin" => $cleanedVariable
                ];
                $headers = [];
                break;
            default:
                abort(422, 'Unsupported verification type.');
        }

        $response = Http::withHeaders($headers)->post($base_url, $payload)->json();

        $data = null;
        
        switch ($verificationType) {
            case 'rc_number':
                if ($response['verificationStatus'] === 'VERIFIED') {
                    $data = $response['response']['cac'];
                }
                break;
        
            case 'bvn':
                if ($response['verificationStatus'] === 'VERIFIED') {
                    $data = $response['response'];

                    if (!empty($data['avatar'])) {
                        $imageData = base64_decode($data['avatar'], true);
                        $finfoOpen = finfo_open();
                        $mimeType = finfo_buffer($finfoOpen, $imageData, FILEINFO_MIME_TYPE);
                        finfo_close($finfoOpen);
                        $extension = ($mimeType === 'image/png') ? 'png' : 'jpg';
                        $fileName = uniqid() . '.' . $extension;
                        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;
                        file_put_contents($filePath, $imageData);
                        $uploadedImage = Util::upload(new \Illuminate\Http\File($filePath), 'trash');
                        if ($uploadedImage) {
                            $data['avatar'] = Util::publicUrl($uploadedImage);
                            unlink($filePath);
                        }
                    }
        
                    if (!empty($data['signature'])) {
                        unset($data['signature']);
                    }
                }
                break;
            case 'nin':
                if ($response['verificationStatus'] === 'VERIFIED') {
                    $data = $response['response'][0];
        
                    if (!empty($data['photo'])) {
                        $imageData = base64_decode($data['photo'], true);
                        $finfoOpen = finfo_open();
                        $mimeType = finfo_buffer($finfoOpen, $imageData, FILEINFO_MIME_TYPE);
                        finfo_close($finfoOpen);
                        $extension = ($mimeType === 'image/png') ? 'png' : 'jpg';
                        $fileName = uniqid() . '.' . $extension;
                        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;
                        file_put_contents($filePath, $imageData);
                        $uploadedImage = Util::upload(new \Illuminate\Http\File($filePath), 'trash');
                        if ($uploadedImage) {
                            $data['photo'] = Util::publicUrl($uploadedImage);
                            unlink($filePath);
                        }
                    }
        
                    if (!empty($data['signature'])) {
                        unset($data['signature']);
                    }
                }
                break;
            case 'tin':
                $responseData = json_decode((json_encode($response)),true);
                if (isset($responseData['ResponseCode']) && $responseData['ResponseCode'] === '001') {
                    $data = $responseData['Taxpayer'];
                }
                break;
            default:
            $data = null;
            break;
        }
        
        if (in_array($verificationType, ['rc_number', 'nin', 'bvn']) && $response['verificationStatus'] !== 'VERIFIED') {
            abort(422, $response['description']);
        }
        
        if ($data) {
            return $data;
        } else {
            abort(422, 'Invalid verification type');
        }
    }

    public function verifyEmail(Request $request)
    {
        $email = $request->email;
        $token = $request->token;
    
        // Find the user with the provided token and email, and ensure the token is not expired
        $user = User::where('otp', $token)
            ->where('email', $email)
            ->where('otp_expires_at', '>=', now())
            ->first();
       
        if ($user) {
            // Update user to mark the email as verified
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->email_verified_at = now();
            $user->is_verified_email = 1;
            $user->save();
    
            // Dispatch the email verified event
            EmailVerified::dispatch($user);
    
            return response()->json(['success' => true], 200);
        }
    
        // Return a JSON response with a 400 status code for invalid or expired token
        return response()->json(['success' => false, 'message' => 'Invalid or expired token.'], 400);
    }

    public function resendPhoneNumberVerification(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|min:10|unique:users,phone_number,' . $request->user()->id,
        ]);
    
        $phone_number = $request->phone_number;
        $code = generate_random_number();
        $expired_at = expires_at();
        $user = $request->user();
        $user->update([
            'phone_number' => $request->phone_number,
            'phone_number_otp' => $code,
            'phone_number_otp_expires_at' => $expired_at
        ]);
    
        Util::sendSMS($phone_number, 'Your OTP code is ' . $code . ' and expires in 10 minutes.', 'single');
    
        return response()->json(['message' => 'Phone number has been resent.'], 200);
    }

    public function confirmPhoneNumberVerification(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|unique:users,phone_number,'. $request->user()->id,
            'otp' => 'required|string',
        ]);

        $res = Util::verifyPhoneNumber($request);
        if($res){
            return response()->json(['message' => 'Phone number verified successfully.'], 200);
        }else{
            return response()->json(['message' => 'Invalid or expired OTP.'], 400);
        }

    }

    public function resendEmailVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->email;
        $code = generate_random_number();
        $expired_at = expires_at();
        $user = User::where('email', $email)->first();
        $user->otp = $code;
        $user->otp_expires_at = $expired_at;
        $user->save();
        //$data = Util::OTPUtils($user, $expired_at, $code);
        //AccountCreated::dispatch($user);
       
        
        //$user->update(['otp' => $code, 'otp_expires_at' => $expired_at]);
        $uniqueToken = Str::random(40);
        $data = [
            "otp" => $code,
            "expires_at" => $expired_at,
            "email" => $user->email,
            "token"=>$uniqueToken,
            "user_type" =>'user',
            "name" => $user->full_name  
        ];
        Mail::to($user->email)->send(new SendMailNoQueue('otp', "Account Verification", $data));
        return response()->json(['message' => 'Email han been resent.'], 200);
    }


    public function changeEmail(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'email' => 'required|email',
        ]);

        $user = $request->user();
        $exists = get_class($user)::where('email',$request->email)->exists();
        if($exists){
            abort(422, 'Email already exists');
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            return abort(422, "Incorrect Password");
        }

        $code = generate_random_number();
        $expired_at = expires_at();
        $user->email = $request->email;
        $data = Util::OTPUtils($user, $expired_at, $code);
        $user_type =  get_user_type($user->user_type);
        $data['email'] = $request->email;
        QueueMail::dispatch($data, 'account_verify', "Email Verification", $user_type);

        return response()->json('Success');
    }

    public function confirmChangeEmail($token){
         $cache = AjoCache::where('token', $token)
                     ->where('expires_at', '>=', now())
                     ->first();

        if (!empty($cache)) {
            // $email = $cache->email;
            $user = User::where('id', $cache->user_id)->first(); 
            if ($user) {
                $code = generate_random_number();
                $expired_at = expires_at();
                $user->old_email = $user->email;
                $user->email = $cache->email;
                $user->email_verified_at = null;
                $user->save();
                $cache->delete();
                $data = Util::OTPUtils($user, $expired_at, $code);
                QueueMail::dispatch($data, 'account_verify', "Email Verification", 'user');
                return view('close');
            } else {
                return view('400', ['message' => 'User not Found.']);
            }
        } else {
            return view('400', ['message' => 'Invalid or expired token.']);
        }
    }

   public function changePassword(Request $request){
        $request->validate([
            'password' => 'required|confirmed|min:6',
            'old_password' => 'required',
        ]);
    

        $user = $request->user();
       // $user = get_class($user)::find($user->id);

        if (!$user || !Hash::check($request->old_password, $user->password)) {
            return abort(422, "Incorrect Password");
        }

        $expired_at = expires_at(30);
        $user->email = $request->email??$user->email;
        $data = Util::OTPUtils($user, $expired_at, Hash::make($request->password), 'password');
        $user_type =  get_user_type($user->user_type);

       
        $data['email'] =  $request->email?? $user->email;
        QueueMail::dispatch($data, 'confirm_change_password', "Confirm Change Password", 'user');
        return response()->json('Success');

   }

    public function confirmChangePassword($token){
        
        $cache = AjoCache::where('token', $token)
        ->where('type','password')
        ->where('expires_at', '>=', Carbon::now()->format("Y-m-d H:i:s"))
        ->first();
   

        if(!empty($cache)) {
            // $email = $cache->email;
        $user = User::where('id', $cache->user_id)->first();

        if ($user) {
   
        $user->password = $cache->data;
        $user->save();
        $cache->delete();

        return view('close');
        } else {
        return view('400', ['message' => 'User not Found.']);
        }
        } else {
        return view('400', ['message' => 'Invalid or expired token.']);
        }
    }

    public function forgotPassword(Request $request)
    {
        
        $request->validate([
            'username' => 'required',
            'user_type' => 'nullable',
        ]);
        $user = null;
        $user = User::where('phone_number', $request->username)->orWhere('email', $request->username)->first();
       
        $otp= mt_rand(100000,999999);
        $otp_expires_at = expires_at(30);
        if ($user) {
            $user->otp =$otp;
            $user->otp_expires_at =  $otp_expires_at;
            $user->save();
            $data = [];
            // $util = Util::GenericUtils($user->email,expires_at(30),,$user?->id, 'user','forgot_password');
            $data['code'] = $otp;
            $data['name'] = $user->full_name;
            $data['email'] = $user->email;
            Mail::to($user->email)->send(new SendMailNoQueue('confirm_forgot_password',  "Confirm Forgot Password", $data));
           // QueueMail::dispatch($data, 'confirm_forgot_password', "Confirm Forgot Password", 'user');
         
            return response()->json('Success');
        }

        return response()->json(['message' => 'User not found.'], 404);
    }

    public function confirmForgotPassword(Request $request)
    {
        // Check if the token exists and is valid

        $request->validate([
            'otp' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);

        $user = User::where('otp', $request->otp)
                    ->where('email', $request->email)
                    ->where('otp_expires_at', '>=', now())
                    ->first();

        // Return error if the token is invalid or expired
        if (!$user) {
            return view('400', ['message' => 'Invalid or expired OTP.']);
        }

        // Reset the password
        $user->password = Hash::make('password'); // default password
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();
        return response()->json(['message' => 'Password reset successfully.']);
    }


    public function validateAccountNumber(Request $request)
    {
        // return $request->all();
        $url = config('default.seamfix.base_url');
        $headers = [
            "apiKey" => config('default.seamfix.account_api_key'),
            "userid" => config('default.seamfix.user_id')
        ];
        $data = [
            "searchParameter" => $request->account_number,
            "bankCode" => $request->bank_code,
            "verificationType" => 'ACCOUNT-INQUIRY-VERIFICATION'
        ];

        return Http::withHeaders($headers)->post($url, $data)->json();
    }

    public function getCorporatesJtb(Request $request)
    {
        $validated = $request->validate([
            'fromdate' => 'required|date_format:d-m-Y',
            'todate' => 'required|date_format:d-m-Y',
        ]);

        $tokenId = $this->getJtbToken();
        $url = "https://api.jtb.gov.ng:8891/api/SBIR/NonIndividual?tokenid=" . $tokenId;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, [
            'fromdate' => $validated['fromdate'],
            'todate' => $validated['todate'],
        ]);

        return response()->json($response->json(), $response->status());
    }

    public function generateTin($data)
    {
        $data = [
            'bvn' => $data['bvn'],
            'title' => $data['gender'] == 'Male' ? 'Mr.' : 'Mrs.',
            'firstName' => $data['firstName'],
            'middleName' => $data['middleName'],
            'lastName' => $data['lastName'],
            'gender' => $data['gender'],
            'stateOfOrigin' => $data['stateOfOrigin'],
            'dob' => $data['dob'],
            'occupation' => $data['occupation'],
            'nationality' => $data['nationality'],
            'email' => $data['email'],
            'phone1' => $data['phone'],
            'photo' => $data['photo'],
            'houseNo' => $data['houseNo'],
            'streetName' => $data['streetName'],
            'lga' => $data['lga'],
            'state' => $data['stateOfOrigin'],
            'country' => $data['country'],
        ];


        // Get Token
        $token = $this->getJtbToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate token. Please try again.',
            ], 500);
        }

        // Prepare TIN generation API request
        $url = "https://api.jtb.gov.ng:8016/api/NIBSS/TinGeneration?tokenid={$token}";

        $response = Http::post($url, $data);

        // Handle response
        if ($response->successful()) {
            return response()->json([
                'success' => true,
                'data' => $response->json(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $response->json()['ResponseDescription'] ?? 'Failed to generate TIN',
        ], 400);
    }

    public function getLogs(Request $request)
    {
        $user = $request->user();
        //delete activity log older than 1 month
        $user->activitylogs()->where('created_at', '<=', Carbon::now()->subMonth())->delete();
        $logs = $user->activitylogs()->latest()->take(500)->paginate(5);
        return response()->json($logs);
    }

    public function recentTransactions(Request $request)
    {
        $user = $request->user();
        $ajo_ids = AjoMember::where('user_id', $user->id)->pluck('ajo_id');
        $wallet_ids = Wallet::whereIn('holder_id', $ajo_ids)->where('holder_type',Ajo::class)->pluck('id');
        $transactions = WalletTransaction::whereIn('wallet_id', $wallet_ids)->latest()->paginate(config('default.pagination_length'));
        return response()->json(WalletTransactionResource::collection($transactions));
    }

    public function guideUpdate(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'type_id' => 'required',
        ]);

        $user = $request->user();
        $type = null;
        $allTypesCount = 0;
        switch ($request->type) {
            case 'ajo':
                 $type =  Ajo::find('id', $request->type_id);
                 $type->update(['guide' => $type->guide+1]);
                 $allTypesCount = Ajo::where('user_id', $user->id)->sum('guide');
                 if($allTypesCount > 5){
                    $user->ajo_guide = 0;
                  } 
                break;
            case 'wallet':
                $user->update(['wallet_guide' => $user->ajo_guide+1]);
                break;
            default:
                return response()->json(['message' => 'Invalid type.'], 422);
        }
        if(empty($type)){
            return response()->json([]);
        }

        return response()->json([]);
      
    }
}
