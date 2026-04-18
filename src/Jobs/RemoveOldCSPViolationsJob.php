<?php
namespace Signify\SecurityHeaders\Jobs;

use DateInterval;
use DateTime;
use Signify\SecurityHeaders\Models\CSPViolation;
use Signify\SecurityHeaders\Reports\CSPViolationsReport;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\DataList;
use Symbiote\QueuedJobs\Services\AbstractQueuedJob;
use Symbiote\QueuedJobs\Services\QueuedJobService;
use Signify\SecurityHeaders\Tasks\RemoveOldCSPViolationsTask;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * A queued job to remove old CSPViolation objects.
 */
class RemoveOldCSPViolationsJob extends AbstractQueuedJob
{
    public function __construct()
    {
        $this->totalSteps = CSPViolation::get()->count();
    }

    public function getTitle()
    {
        return _t(
            __CLASS__ . '.TITLE',
            'Remove old CSP violations'
        );
    }

    public function getJobType()
    {
        return \Symbiote\QueuedJobs\Services\QueuedJob::QUEUED;
    }

    public function process()
    {
        $task = new RemoveOldCSPViolationsTask();
        $output = PolyOutput::create(PolyOutput::FORMAT_ANSI, new NullOutput());
        $task->run($output, $output);
        $this->isComplete = true;
    }
}
