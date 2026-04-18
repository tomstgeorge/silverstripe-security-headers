<?php

namespace Signify\SecurityHeaders\Tasks;

use Signify\SecurityHeaders\Jobs\RemoveUnreferencedCSPDocumentJob;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use Symbiote\QueuedJobs\Services\QueuedJobService;

class RemoveUnreferencedCSPDocumentsTask extends BuildTask
{
    private static $title = 'Remove unreferenced CSP Document URIs';

    private static $description =
    'CSP Document URIs that are not referenced by a CSP violation report can be safely removed.';

    /**
     * {@inheritDoc}
     * @see \SilverStripe\Dev\BuildTask::run()
     */
    public function run(HTTPRequest|PolyOutput $request, PolyOutput|null $output = null): bool
    {
        $deletionJob = new RemoveUnreferencedCSPDocumentJob();

        $jobId = singleton(QueuedJobService::class)->queueJob($deletionJob);

        $output?->writeln("Job queued with ID $jobId");
        return true;
    }

    public function isEnabled()
    {
        return parent::isEnabled() && class_exists(QueuedJobService::class);
    }
}
