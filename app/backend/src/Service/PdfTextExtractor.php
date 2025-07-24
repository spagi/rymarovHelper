<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PdfTextExtractor
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private Filesystem $filesystem;

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger, Filesystem $filesystem)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->filesystem = $filesystem;
    }

    public function extractTextFromUrl(string $pdfUrl): ?string
    {
        $tempPdfPath = null;
        try {
            $response = $this->httpClient->request('GET', $pdfUrl);
            $pdfContent = $response->getContent();

            $tempPdfPath = $this->filesystem->tempnam(sys_get_temp_dir(), 'pdf_');
            $this->filesystem->dumpFile($tempPdfPath, $pdfContent);

            $process = new Process(['pdftotext', '-layout', $tempPdfPath, '-']);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            return $process->getOutput();
        } catch (HttpExceptionInterface $e) {
            $this->logger->error(sprintf('Failed to download PDF from %s: %s', $pdfUrl, $e->getMessage()));
            return null;
        } catch (ProcessFailedException $e) {
            $this->logger->error(sprintf('Failed to extract text from PDF: %s. Error output: %s', $e->getMessage(), $e->getProcess()->getErrorOutput()));
            return null;
        } catch (\Exception $e) {
            $this->logger->error(sprintf('An unexpected error occurred during PDF text extraction: %s', $e->getMessage()));
            return null;
        } finally {
            if ($tempPdfPath && $this->filesystem->exists($tempPdfPath)) {
                $this->filesystem->remove($tempPdfPath);
            }
        }
    }
}
