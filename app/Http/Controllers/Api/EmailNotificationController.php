<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\EmailNotification;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EmailNotificationController extends Controller
{
    const DEFAULT_PAGE = 10;

    public function getAll(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'page' => 'integer|max:100',
            ]);

            if($validator->fails()) {
                return $this->sendApiResponse(Response::HTTP_UNPROCESSABLE_ENTITY, __('validation failed'), null, $validator->errors());
            }
            $page = $request->page ?? static::DEFAULT_PAGE;
            $data = EmailNotification::limit($page)->get();
            return $this->sendApiResponse(Response::HTTP_OK, __('Records retrieved successfully'), $data);
        } catch(Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return $this->sendApiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, __('An error occured while fetching data'));
        }
    }

    public function get($uuid) {
        try {
            $data = EmailNotification::where('uuid', $uuid)->first();
            if(!$data) {
                return $this->sendApiResponse(Response::HTTP_NOT_FOUND, __('Record not found'));
            }
            return $this->sendApiResponse(Response::HTTP_OK, __('Record retrieved successfully'), $data);
        } catch(Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return $this->sendApiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, __('An error occured while fetching data'));
        }
    }

    public function create(Request $request) {
        try {
            $data = $request->only(['from', 'from_name', 'to', 'subject', 'body', 'html_body', 'attachments']);
            $validator = Validator::make($data, [
                'from' => 'required|string|email',
                'from_name' => 'required|string',
                'to' => 'required|string|email',
                'subject' => 'required|string|max:100',
                'body' => 'required|string',
                'html_body' => 'nullable|string',
                'attachments.*' => 'nullable|mimes:jpg,png,jpeg,doc,docx,pdf,txt,xls,xlsx'
            ]);

            if($validator->fails()) {
                return $this->sendApiResponse(Response::HTTP_UNPROCESSABLE_ENTITY, __('validation failed'), null, $validator->errors());
            }

            $createdData = EmailNotification::createNotification($data);
            return $this->sendApiResponse(Response::HTTP_CREATED, __('Email posted successfully'), $createdData);
        } catch (Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return $this->sendApiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, __('An error occured while posting'));
        }
    }

    public function getEmailStats() {
        try {
            $stats = EmailNotification::getStats();
            return $this->sendApiResponse(Response::HTTP_OK, __('Stats fetched successfully'), $stats);
        } catch (Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return $this->sendApiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, __('An error occured while fetching'));
        }
    }

}
