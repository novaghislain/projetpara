<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('client_contacts', function (Blueprint $table) {
            if (!Schema::hasColumn('client_contacts', 'phone_mobile')) $table->string('phone_mobile')->nullable()->after('phone');
            if (!Schema::hasColumn('client_contacts', 'phone_whatsapp')) $table->string('phone_whatsapp')->nullable()->after('phone_mobile');
            if (!Schema::hasColumn('client_contacts', 'email_secondary')) $table->string('email_secondary')->nullable()->after('email');
            if (!Schema::hasColumn('client_contacts', 'adresse')) $table->string('adresse')->nullable()->after('email_secondary');
            if (!Schema::hasColumn('client_contacts', 'ville')) $table->string('ville')->nullable()->after('adresse');
            if (!Schema::hasColumn('client_contacts', 'pays')) $table->string('pays')->nullable()->after('ville');
            if (!Schema::hasColumn('client_contacts', 'photo')) $table->string('photo')->nullable()->after('pays');
            if (!Schema::hasColumn('client_contacts', 'notes')) $table->text('notes')->nullable()->after('photo');
            if (!Schema::hasColumn('client_contacts', 'tags')) $table->json('tags')->nullable()->after('notes');
            if (!Schema::hasColumn('client_contacts', 'anniversaire')) $table->date('anniversaire')->nullable()->after('tags');
            if (!Schema::hasColumn('client_contacts', 'linkedin')) $table->string('linkedin')->nullable()->after('anniversaire');
        });
    }
    public function down(): void {
        Schema::table('client_contacts', function (Blueprint $table) {
            $table->dropColumn(['phone_mobile','phone_whatsapp','email_secondary','adresse','ville','pays','photo','notes','tags','anniversaire','linkedin']);
        });
    }
};
