<?php

require __DIR__.'/payroll_self_service.php';

use App\Models\{Payroll, PayrollHistory, Payslip};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

$as(1);
$run->refresh();
try { $service->generatePayslips($run,1); throw new RuntimeException('Draft run generated payslips.'); }
catch (Illuminate\Validation\ValidationException $e) {}
$run->update(['status'=>'approved']);
$history=PayrollHistory::where('payroll_id',$run->id)->where('user_id',3)->firstOrFail();
$snapshot=$history->snapshot;
$snapshot['total_deductions']=4321.75;
$snapshot['net_pay']=12345.67;
$snapshot['total_in_hand']=12645.67;
$snapshot['ctc']=20001.23;
$history->update(['snapshot'=>$snapshot,'total_deductions'=>4321.75]);
$service->generatePayslips($run->fresh(),1);
$slip=Payslip::where('payroll_id',$run->id)->where('user_id',3)->firstOrFail();
check((float)$slip->total_deductions===4321.75 && (float)$slip->net_salary===12345.67, 'Payslip replaced saved formula deductions/net with guessed totals.');
check($slip->employee_snapshot['total_in_hand']===12645.67 && $slip->generated_at!==null, 'Saved payout snapshot or generation date missing.');
$page=$controller->payslips(Request::create('/payroll/payslips','GET',['month'=>10,'year'=>2026]))->getData();
$html=$render('admin.payroll.payslips',$page);
foreach (['Oct 2026','GOOGLE-EMP-0002','12,645.67','20,001.23'] as $text) check(str_contains($html,$text),'Real payslip list value missing: '.$text);
check(!str_contains($html,'Jan 2026'),'Payslip list fabricated a January period.');
$empty=$controller->payslips(Request::create('/payroll/payslips','GET',['month'=>2,'year'=>2020]))->getData();
check($empty['payslips']->isEmpty() && str_contains($render('admin.payroll.payslips',$empty),'No payslips available'),'Empty filter fabricated payslips.');
// A missing employee slip must be recovered even if unrelated duplicates keep the counts equal.
$missing=Payslip::where('payroll_id',$run->id)->where('user_id',4)->firstOrFail();
$missing->forceDelete();
$duplicate=$slip->replicate(); $duplicate->payslip_number.='-legacy-copy'; $duplicate->save();
$saved=$slip->getRawOriginal('employee_snapshot');
$controller->payslips(Request::create('/payroll/payslips','GET'));
check(Payslip::where('payroll_id',$run->id)->where('user_id',4)->count()===1,'Matching counts hid a missing employee payslip.');
check($slip->fresh()->getRawOriginal('employee_snapshot')===$saved,'Dashboard recovery overwrote a saved payslip.');
$service->finalizePayroll($run->fresh(),1);
$locked=$slip->fresh(); $lockedSnapshot=$locked->getRawOriginal('employee_snapshot');
check($locked->locked_at!==null,'Finalized payslip was not locked.');
$changed=$history->fresh()->snapshot; $changed['net_pay']=1; $history->update(['snapshot'=>$changed]);
$service->generatePayslips($run->fresh(),1);
check($slip->fresh()->getRawOriginal('employee_snapshot')===$lockedSnapshot,'Finalized payslip was silently rewritten from later data.');
// A removed document must stay removed rather than reappear when the list is refreshed.
$removed=Payslip::where('payroll_id',$run->id)->where('user_id',4)->firstOrFail(); $removed->delete();
$controller->payslips(Request::create('/payroll/payslips','GET'));
check(Payslip::where('payroll_id',$run->id)->where('user_id',4)->count()===0,'Deleted payslip was regenerated on refresh.');
$run->update(['status'=>'calculated']);
check($controller->payslips(Request::create('/payroll/payslips','GET'))->getData()['payslips']->isEmpty(),'Unapproved payroll appeared as approved payslips.');
$incomplete=$run->replicate(); $incomplete->status='approved'; $incomplete->save();
$incompleteHistory=PayrollHistory::create(['payroll_id'=>$incomplete->id,'user_id'=>3,'company_id'=>3,'snapshot'=>['employee_name'=>'No saved calculation']]);
check($service->generatePayslips($incomplete,1)===0 && !Payslip::where('payroll_id',$incomplete->id)->exists(),'Incomplete history generated a zero-filled placeholder.');
$incompleteHistory->update(['snapshot'=>['gross_salary'=>0,'total_deductions'=>0,'net_pay'=>0,'total_in_hand'=>0,'ctc'=>0]]);
check($service->generatePayslips($incomplete,1)===1,'A genuine saved zero-pay calculation was discarded.');
DB::table('users')->insert(['id'=>99,'name'=>'Foreign employee','role'=>'employee','company_id'=>99,'email'=>'foreign@example.test']);
PayrollHistory::create(['payroll_id'=>$incomplete->id,'user_id'=>99,'company_id'=>99,'snapshot'=>['gross_salary'=>1000,'net_pay'=>1000]]);
$service->generatePayslips($incomplete,1,true);
check(!Payslip::where('payroll_id',$incomplete->id)->where('user_id',99)->exists(),'Generation copied another company employee into this payroll.');
echo "PASS: actual periods/employee IDs/payouts, saved formula totals, empty states, missing-employee recovery, snapshot preservation, finalized locks and deleted/unapproved document filtering.\n";
