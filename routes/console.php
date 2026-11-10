<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;



Schedule::command('app:expire-customer-booking')->dailyAt('11:32')->timezone('Asia/Jakarta');
Schedule::command('marketing:notifikasi-cicilan-sales')->dailyAt('09:00')->timezone('Asia/Jakarta');