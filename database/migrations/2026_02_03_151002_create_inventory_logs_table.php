                                <?php

                                use Illuminate\Database\Migrations\Migration;
                                use Illuminate\Database\Schema\Blueprint;
                                use Illuminate\Support\Facades\Schema;

                                return new class extends Migration {
                                    public function up(): void
                                    {
                                        Schema::create('inventory_logs', function (Blueprint $table) {
                                            $table->id();
                                            $table->foreignId('ingredient_id')->constrained()->onDelete('cascade');
                                            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

                                            $table->decimal('quantity_change', 10, 3); // Negative for usage, Positive for restock
                                            $table->string('action'); // 'production', 'purchase', 'wastage', 'adjustment'
                                            $table->string('reason')->nullable();

                                            // Optional: Link to a specific production log if action is 'production'
                                            $table->foreignId('production_log_id')->nullable()->constrained()->onDelete('cascade');

                                            // Audit snapshots
                                            $table->decimal('stock_before', 10, 3);
                                            $table->decimal('stock_after', 10, 3);

                                            $table->timestamps();
                                        });
                                    }

                                    public function down(): void
                                    {
                                        Schema::dropIfExists('inventory_logs');
                                    }
                                };
