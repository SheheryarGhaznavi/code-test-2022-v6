<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{

    /**
     * @var BookingRepository
     */
    protected $repository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $response = [];

        if ($user_id = $request->get('user_id')) {
            $response = $this->repository->getUsersJobs($user_id);

        } elseif ($request->__authenticatedUser->user_type == env('ADMIN_ROLE_ID') || $request->__authenticatedUser->user_type == env('SUPERADMIN_ROLE_ID')) {
            $response = $this->repository->getAll($request);
        }

        return response($response);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        return response($this->repository->with('translatorJobRel.user')->find($id));
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        return response($this->repository->store($request->__authenticatedUser, $request->all()));

    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update($id, Request $request)
    {
        return response($this->repository->updateJob($id, array_except($request->all(), ['_token', 'submit']), $request->__authenticatedUser));
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        return response($this->repository->storeJobEmail($request->all()));
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        return ($request->get('user_id') ? response($this->repository->getUsersJobsHistory($request->get('user_id'), $request)) : null);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request)
    {
        return response($this->repository->acceptJob($request->all(), $request->__authenticatedUser));
    }

    public function acceptJobWithId(Request $request)
    {
        return response($this->repository->acceptJobWithId($request->get('job_id'), $request->__authenticatedUser));
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request)
    {
        return response($this->repository->cancelJobAjax($request->all(), $request->__authenticatedUser));
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request)
    {
        return response($this->repository->endJob($request->all()));
    }

    public function customerNotCall(Request $request)
    {
        return response($this->repository->customerNotCall($request->all()));
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {
        return response($this->repository->getPotentialJobs($request->__authenticatedUser));
    }

    public function distanceFeed(Request $request)
    {
        $distance = $request->distance ?? "";
        $time = $request->time ?? "";
        $session = $request->session_time ?? "";
        $jobid = $request->jobid ?? "";
        $admincomment = $request->admincomment ?? "";
        $flagged = 'no';
        $manually_handled = ($request->manually_handled ? "yes" : "no");
        $by_admin = ($request->by_admin ? "yes" : "no");


        if ($request->flagged) {

            if (!$request->admincomment) {
                return "Please, add comment";
            }
            $flagged = 'yes';
        }

        if ($time || $distance) {
            Distance::where('job_id', '=', $jobid)->update(array('distance' => $distance, 'time' => $time));
        }

        if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {
            Job::where('id', '=', $jobid)->update([
                'admin_comments' => $admincomment,
                'flagged' => $flagged,
                'session_time' => $session,
                'manually_handled' => $manually_handled,
                'by_admin' => $by_admin
            ]);
        }

        return response('Record updated!');
    }

    public function reopen(Request $request)
    {
        return response($this->repository->reopen($request->all()));
    }

    public function resendNotifications(Request $request)
    {
        $job = $this->repository->find($request->jobid);
        $this->repository->sendNotificationTranslator($job, $this->repository->jobToData($job), '*');

        return response(['success' => 'Push sent']);
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        try {
            $this->repository->sendSMSNotificationToTranslator($this->repository->find($request->jobid));
            return response(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            return response(['success' => $e->getMessage()]);
        }
    }

}