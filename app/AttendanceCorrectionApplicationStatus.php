<?php

namespace App;

enum AttendanceCorrectionApplicationStatus: int
{
    case Pending  = 0;
    case Approved = 1;
}
