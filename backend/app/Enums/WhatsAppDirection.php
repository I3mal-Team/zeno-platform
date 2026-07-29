<?php

declare(strict_types=1);

namespace App\Enums;

/** Matches the `direction` CHECK constraint on `whatsapp_handoffs`. */
enum WhatsAppDirection: string
{
    case CandidateToEmployer = 'candidate_to_employer';
    case EmployerToCandidate = 'employer_to_candidate';
}
