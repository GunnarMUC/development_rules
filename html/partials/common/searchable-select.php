<?php
/**
 * Searchable Select Partial (Alpine.js replacement for Select2)
 *
 * Variables:
 * - $field_name: The name attribute for the select field
 * - $field_id: The id attribute for the select field
 * - $options: Array of options [['value' => 'x', 'label' => 'y'], ...]
 * - $selected: Currently selected value (optional)
 * - $placeholder: Placeholder text (default: 'Select an option')
 * - $allow_empty: Whether to show an empty option (default: true)
 */

$field_name = $field_name ?? 'select_field';
$field_id = $field_id ?? $field_name;
$options = $options ?? [];
$selected = $selected ?? '';
$placeholder = $placeholder ?? 'Select an option';
$allow_empty = $allow_empty ?? true;
?>

<div x-data="{
    open: false,
    search: '',
    selected: '<?php echo htmlspecialchars($selected); ?>',
    options: <?php echo json_encode($options); ?>,

    get filteredOptions() {
        if (!this.search) return this.options;
        return this.options.filter(option =>
            option.label.toLowerCase().includes(this.search.toLowerCase())
        );
    },

    get selectedLabel() {
        if (!this.selected) return '<?php echo htmlspecialchars($placeholder); ?>';
        const option = this.options.find(opt => opt.value == this.selected);
        return option ? option.label : '<?php echo htmlspecialchars($placeholder); ?>';
    },

    selectOption(value) {
        this.selected = value;
        this.open = false;
        this.search = '';
    }
}"
class="position-relative"
@click.away="open = false">

    <!-- Hidden input for form submission -->
    <input type="hidden"
           name="<?php echo htmlspecialchars($field_name); ?>"
           :value="selected">

    <!-- Display button -->
    <button type="button"
            class="form-select text-start"
            @click="open = !open"
            style="cursor: pointer;">
        <span x-text="selectedLabel"></span>
    </button>

    <!-- Dropdown menu -->
    <div x-show="open"
         x-transition
         class="position-absolute w-100 bg-white border rounded shadow-sm mt-1"
         style="z-index: 1050; max-height: 300px; overflow-y: auto;">

        <!-- Search input -->
        <div class="p-2 border-bottom">
            <input type="text"
                   class="form-control form-control-sm"
                   placeholder="Search..."
                   x-model="search"
                   @click.stop>
        </div>

        <!-- Options list -->
        <div class="list-group list-group-flush">
            <?php if ($allow_empty): ?>
            <button type="button"
                    class="list-group-item list-group-item-action"
                    :class="{ 'active': selected === '' }"
                    @click="selectOption('')">
                <?php echo htmlspecialchars($placeholder); ?>
            </button>
            <?php endif; ?>

            <template x-for="option in filteredOptions" :key="option.value">
                <button type="button"
                        class="list-group-item list-group-item-action"
                        :class="{ 'active': selected == option.value }"
                        @click="selectOption(option.value)"
                        x-text="option.label"></button>
            </template>

            <div x-show="filteredOptions.length === 0" class="p-3 text-muted text-center">
                No results found
            </div>
        </div>
    </div>
</div>
