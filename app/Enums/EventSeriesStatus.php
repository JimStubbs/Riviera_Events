<?php

namespace App\Enums;

enum EventSeriesStatus: string
{
    case Draft = 'draft';
    case PendingEmailVerification = 'pending_email_verification';
    case PendingReview = 'pending_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Archived = 'archived';
}
