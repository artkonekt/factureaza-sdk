<?php

declare(strict_types=1);

/**
 * Contains the InvoiceTest class.
 *
 * @copyright   Copyright (c) 2022 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2022-09-17
 *
 */

namespace Konekt\Factureaza\Tests\Integration;

use Konekt\Factureaza\Factureaza;
use Konekt\Factureaza\Models\Invoice;
use Konekt\Factureaza\Models\InvoiceItem;
use Konekt\Factureaza\Requests\CreateInvoice;
use PHPUnit\Framework\TestCase;

class InvoiceTest extends TestCase
{
    /** @test */
    public function it_can_create_an_invoice_in_the_sandbox_environment()
    {
        $api = Factureaza::sandbox();

        $request = CreateInvoice::inSeries('1061104350')
            ->forClient('1064116434')
            ->withEmissionDate('2024-01-17')
            ->withUpperAnnotation('Hello I am on the top')
            ->withLowerAnnotation('Hello I smell the bottom')
            ->addItem(['description' => 'Service', 'price' => 19, 'unit' => 'luna', 'productCode' => '']);

        $invoice = $api->createInvoice($request);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals('2024-01-17', $invoice->documentDate->format('Y-m-d'));
        $this->assertEquals('1064116434', $invoice->clientId);
        $this->assertEquals(19, $invoice->total);
        $this->assertEquals('RON', $invoice->currency);
        $this->assertIsString($invoice->number);
        $this->assertIsString($invoice->hashcode);
        $this->assertEquals('Hello I am on the top', $invoice->upperAnnotation);
        $this->assertEquals('Hello I smell the bottom', $invoice->lowerAnnotation);

        $this->assertCount(1, $invoice->items);

        $item = $invoice->items[0];
        $this->assertInstanceOf(InvoiceItem::class, $item);
        $this->assertEquals('Service', $item->description);
        $this->assertEquals(19, $item->price);
        $this->assertEquals('luna', $item->unit);
        $this->assertEquals('', $item->productCode);
        $this->assertEquals(1, $item->quantity);
    }

    /** @test */
    public function it_can_retrieve_invoices_as_pdf_in_base64_format()
    {
        $pdf = Factureaza::sandbox()->invoiceAsPdfBase64('1065254625');
        $this->assertIsString($pdf);
        $this->assertStringStartsWith('%PDF', base64_decode($pdf));
    }

    /** @test */
    public function it_can_retrieve_an_invoice_by_id()
    {
        $invoice = Factureaza::sandbox()->invoice('1065254625');

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals('2023-12-02', $invoice->documentDate->format('Y-m-d'));
        $this->assertEquals('1064116436', $invoice->clientId);
        $this->assertEquals(11961.88, $invoice->total);
        $this->assertEquals('RON', $invoice->currency);
        $this->assertEquals('SRV-1001', $invoice->number);
        $this->assertEquals('eb50437e-a9de-11ee-a149-f45653f3', $invoice->hashcode);
        $this->assertNull($invoice->upperAnnotation);
        $this->assertEquals("Sperăm intr-o colaborare fructuoasă şi pe viitor.\n Cu stimă maximă și virtute absolută, Ion Pop S.C. DEMO IMPEX S.R.L.", $invoice->lowerAnnotation);

        $this->assertCount(2, $invoice->items);

        $item = $invoice->items[0];
        $this->assertInstanceOf(InvoiceItem::class, $item);
        $this->assertEquals('Servicii suport cf. ctc. 3482/14.12.2023', $item->description);
        $this->assertEquals(178, $item->price);
        $this->assertEquals('ore', $item->unit);
        $this->assertNull($item->productCode);
        $this->assertEquals(11, $item->quantity);
    }

    /** @test */
    public function a_newly_created_invoice_is_open_by_default()
    {
        $api = Factureaza::sandbox();

        $request = CreateInvoice::inSeries('1061104350')
            ->forClient('1064116434')
            ->withEmissionDate('2024-01-17')
            ->addItem(['description' => 'Service', 'price' => 19, 'unit' => 'luna', 'productCode' => '']);

        $invoice = $api->createInvoice($request);

        $this->assertTrue($invoice->state->isOpen(), 'The invoice is not in open state by default');
    }

    /** @test */
    public function a_draft_invoice_can_be_explicitly_requested()
    {
        $api = Factureaza::sandbox();

        $request = CreateInvoice::inSeries('1061104350')
            ->forClient('1064116434')
            ->withEmissionDate('2024-01-17')
            ->asDraft()
            ->addItem(['description' => 'Service', 'price' => 19, 'unit' => 'luna', 'productCode' => '']);

        $invoice = $api->createInvoice($request);

        $this->assertTrue($invoice->state->isDraft(), 'The invoice should be a draft when explicitly requested');
    }
}
