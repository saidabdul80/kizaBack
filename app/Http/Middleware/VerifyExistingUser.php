<?php

namespace App\Http\Middleware;

use App\Events\AccountCreated;
use Closure;
use App\Models\IndividualTaxPayer;
use App\Models\CorporateTaxPayer;
use App\Jobs\SendOtpEmail;

class VerifyExistingUser
{
    public function handle($request, Closure $next)
    {
        
        return $next($request);
    }
}
