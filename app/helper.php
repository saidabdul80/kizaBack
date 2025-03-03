<?php



use App\Services\Util;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// function authorize($user, $permission, $req = null, $throwError = true): bool
// {

//     $isAuthorized = false;

//     switch ($permission) {
//         case CAN_ENROLL:
//             $isAuthorized = Util::canPerformEnrollment($user, $req);
//             break;
//         case CAN_COLLECT_TAX:
//             $isAuthorized = Util::canCollectPayment($user, $req);
//             break;
//         default:
//             $isAuthorized = $user ? $user->hasPermissionTo($permission) : false;
//             break;
//     }

//     if ($isAuthorized) {
//         return true;
//     }
//     if ($throwError) {
//         abort(403, "Permission Denied");
//     }
//     return false;
// }

function authorize($user, $permission, $req = null, $throwError = true): bool
{
    $isAuthorized = false;

    if (is_array($permission)) {
        foreach ($permission as $perm) {
            if (checkPermission($user, $perm, $req)) {
                $isAuthorized = true;
                break;
            }
        }
    } else {
        $isAuthorized = checkPermission($user, $permission, $req);
    }

    if ($isAuthorized) {
        return true;
    }
    
    if ($throwError) {
        abort(403, "Permission Denied");
    }
    
    return false;
}


function checkPermission($user, $permission, $req = null): bool
{
    return $user ? $user->hasPermissionTo($permission) : false;
}



/**
 * Get the date range from the request.
 *
 * This function retrieves the date range from the given request.
 * If the date is provided as an array, it parses and formats the start and end dates.
 * If the date is provided as a single value, it returns the formatted date.
 * If no date is provided, it defaults to the start and end dates of the current year.
 *
 * @param \Illuminate\Http\Request $request The request object containing the date information.
 * @return array|string An array with the start and end dates or a single formatted date string.
 */
function get_date_range($request)
{

    if ($request->date) {
        if(is_array($request->date)) {
            return $request->date;
        }else{
            return explode(',', $request->date);
        }
    }

    $endDate = $request->end_date;
    $startDate = $request->start_date;
    $date = [];
    if ($startDate) {
        $date[] = Carbon::parse($startDate)->format('Y-m-d');
    }

    if ($endDate) {
        $date[] = Carbon::parse($endDate)->format('Y-m-d');
    }

    if (empty($startDate) && empty($endDate)) {
        $date = [
            Carbon::now()->startOfMonth()->format('Y-m-d'),
            Carbon::now()->endOfMonth()->format('Y-m-d'),
        ];
    }

    return $date;
}

function get_date_range_prev($dateRange)
{
    $startDate = Carbon::parse($dateRange[0]);
    $endDate = Carbon::parse($dateRange[1]);

    $diffInMonths = $startDate->diffInMonths($endDate) + 1;

    $prevStartDate = $startDate->subMonths($diffInMonths);
    $prevEndDate = $endDate->subMonths($diffInMonths);

    return  [
        $prevStartDate->format('Y-m-d'),
        $prevEndDate->format('Y-m-d')
    ];
}


function this_month($request)
{
    return $request->date ?? [
        Carbon::now()->startOfMonth()->format('Y-m-d'),
        Carbon::now()->endOfMonth()->format('Y-m-d'),
    ];
}

function get_date_range_from($from)
{
    $registeredYear = Carbon::parse($from)->year;

    $currentYear = Carbon::now()->year;
    $yearsRange = range($registeredYear, $currentYear);
    $yearsData = [];
    foreach ($yearsRange as $year) {
        $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear()->format('Y-m-d');
        $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear()->format('Y-m-d');
        $yearsData[] = [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }
    return $yearsData;
}


function validate_phone_number($phoneNumber)
{
    // Remove any non-numeric characters
    $phoneNumber = preg_replace('/\D/', '', str_replace(' ', '', $phoneNumber));

    // Check if the number starts with '0' and remove it
    if (substr($phoneNumber, 0, 1) === '0') {
        $phoneNumber = substr($phoneNumber, 1); // Remove the leading '0'
    }

    // Add '234' at the beginning if not already present
    if (substr($phoneNumber, 0, 3) !== '234') {
        $phoneNumber = '234' . $phoneNumber;
    }

    // Check if the modified number starts with '234'
    if (substr($phoneNumber, 0, 3) === '234') {
        return $phoneNumber;
    } else {
        return null;
    }
}


function generate_random_number($length = 5)
{
    return str_pad(mt_rand(0, 99999), $length, '0', STR_PAD_LEFT);
}

function expires_at($t = 30)
{
    return Carbon::now()->addMinutes($t)->format("Y-m-d H:i:s");
}

function get_user_type($user_type)
{
    $userType = '';
    switch ($user_type) {
        case 'individualTaxPayer':
            $userType = "individual";
            break;
        case 'corporateTaxPayer':
            $userType = "corporate";
            break;
        default:
            $userType = $user_type;
            break;
    }
    return $userType;
}



function getBase64Image($path)
{
    if (filter_var($path, FILTER_VALIDATE_URL)) {
        try {
            $response = Http::get($path);

            if ($response->failed()) {
            //    Log::info("Failed to fetch remote image, HTTP status code: " . $response->status());
                return '';
            }

            $imageData = $response->body();
            $mimeType = $response->header('Content-Type');
            $base64 = base64_encode($imageData);
            return 'data:' . $mimeType . ';base64,' . $base64;
        } catch (\Exception $e) {
            Log::info("Error fetching remote image: " . $e->getMessage());
            return '';
        }
    }

    $fullPath = public_path($path);

    if (!is_file($fullPath)) {
        Log::info("File not found: " . $fullPath);
        return '';
    }

    try {
        $imageData = file_get_contents($fullPath);
        $base64 = base64_encode($imageData);
        $mimeType = mime_content_type($fullPath);
        return 'data:' . $mimeType . ';base64,' . $base64;
    } catch (\Exception $e) {
        Log::info("Error processing image: " . $e->getMessage());
        return '';
    }
}

function get_remote_mime_type($url)
{
    // Get the MIME type of a remote image
    $headers = get_headers($url, 1);
    return $headers['Content-Type'];
}

function maskPhoneNumber($phoneNumber) {
    $length = strlen($phoneNumber);

    if ($length <= 6) {
        return $phoneNumber; // Not enough digits to mask
    }

    $start = substr($phoneNumber, 0, 3);
    $end = substr($phoneNumber, -3);

    // Calculate the number of asterisks needed
    $maskedSection = str_repeat('*', $length - 6);

    return $start . $maskedSection . $end;
}

function toTitleCase($str) {
    return implode(' ', array_map(function ($word) {
        return strtoupper(substr($word, 0, 1)) . substr($word, 1);
    }, explode(' ', $str)));
}

function extractVariables($template){
    preg_match_all('/{(\w+)}/', $template, $matches);
    $response = [];
    foreach ($matches[1] as $key) {
        $response[$key] = 0;
    }
    if(empty($response)){
        return (object)[];
    }
    return $response;
}
