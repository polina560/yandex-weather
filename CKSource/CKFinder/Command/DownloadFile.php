<?php

/*
 * CKFinder
 * ========
 * https://ckeditor.com/ckfinder/
 * Copyright (c) 2007-2023, CKSource Holding sp. z o.o. All rights reserved.
 *
 * The software, this file and its contents are subject to the CKFinder
 * License. Please read the license.txt file before using, installing, copying,
 * modifying or distribute this file or part of its contents. The contents of
 * this file is part of the Source Code of CKFinder.
 */

namespace CKSource\CKFinder\Command;

use CKSource\CKFinder\{Acl\Permission, Event\CKFinderEvent, Event\DownloadFileEvent, Exception\AccessDeniedException};
use Exception;
use CKSource\CKFinder\Filesystem\{File\DownloadedFile, Folder\WorkingFolder};
use Symfony\Component\{EventDispatcher\EventDispatcher, HttpFoundation\Request, HttpFoundation\StreamedResponse};

class DownloadFile extends CommandAbstract
{
    protected array $requires = [Permission::FILE_VIEW];

    /**
     * @throws AccessDeniedException
     * @throws Exception
     */
    public function execute(
        Request $request,
        WorkingFolder $workingFolder,
        EventDispatcher $dispatcher
    ): StreamedResponse {
        $fileName = (string)$request->query->get('fileName');

        $downloadedFile = new DownloadedFile($fileName, $this->app);

        $downloadedFile->isValid();

        $downloadedFileEvent = new DownloadFileEvent($this->app, $downloadedFile);

        $dispatcher->dispatch($downloadedFileEvent, CKFinderEvent::DOWNLOAD_FILE);

        if ($downloadedFileEvent->isPropagationStopped()) {
            throw new AccessDeniedException();
        }

        $response = new StreamedResponse();

        $response->headers->set('Cache-Control', 'cache, must-revalidate');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Expires', '0');

        if ('text' === $request->get('format')) {
            $response->headers->set('Content-Type', 'text/plain; charset=utf-8');
        } else {
            $userAgent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
            $encodedName = str_replace('"', '\\"', $fileName);
            if (str_contains($userAgent, 'MSIE')) {
                $encodedName = str_replace(['+', '%2E'], [' ', '.'], urlencode($encodedName));
            }
            $response->headers->set('Content-Type', 'application/octet-stream; name="' . $fileName . '"');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $encodedName . '"');
        }

        $response->headers->set('Content-Length', $downloadedFile->getSize());

        $fileStream = $workingFolder->readStream($downloadedFile->getFilename());
        $chunkSize = 1024 * 100; // how many bytes per chunk

        $response->setCallback(function () use ($fileStream, $chunkSize) {
            if (!$fileStream) {
                return false;
            }
            while (!feof($fileStream)) {
                echo fread($fileStream, $chunkSize);
                flush();
                @set_time_limit(8);
            }

            return true;
        });

        return $response;
    }
}
