<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vendor_id');
            $table->foreignId('node_id');

            $table->text('name');
            $table->smallInteger('cpNumber');
            $table->text('capacity')->nullable();
            $table->text('linkFrom')->nullable();
            $table->text('linkTo')->nullable();
            $table->text('doco')->nullable();
            $table->text('lastMile')->nullable();
            $table->double('annualInvoiceValue');
            $table->float('sharePercent');
            $table->float('discountOffered');
            $table->float('annualVendorValue');
            $table->float('unitRate');
            $table->smallInteger('totalPODays');
            $table->text('remarks')->nullable();

            $table->text('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('services');
    }
};
