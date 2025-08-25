jQuery(document).ready(function($) {
    // Show/hide slider type fields
    function toggleSliderFields() {
        $('.glint-slider-type').hide();
        const type = $('#slider_type').val();
        if (type) $(`.glint-slider-type.${type}`).show();
    }
    
    // Initialize
    toggleSliderFields();
    $('#slider_type').change(toggleSliderFields);
    
    // Media uploader
    $(document).on('click', '.upload-image', function(e) {
        e.preventDefault();
        const button = $(this);
        const imageId = button.siblings('.image-id');
        const preview = button.siblings('.image-preview');
        
        const frame = wp.media({
            title: 'Select Image',
            multiple: false,
            library: { type: 'image' }
        });
        
        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            imageId.val(attachment.id);
            preview.html(`<img src="${attachment.url}" style="max-width:200px;display:block;">`);
        });
        
        frame.open();
    });
    
    // Add new slide
    $('.glint-repeater').on('click', '.add-slide', function(e) {
        e.preventDefault();
        const repeater = $(this).closest('.glint-repeater');
        const itemCount = repeater.find('.glint-repeater-item').length;
        const newItem = repeater.find('.glint-repeater-item').first().clone();
        
        // Reset values
        newItem.find('input, textarea').val('');
        newItem.find('.image-preview').html('');
        newItem.find('h4').text(`Slide #${itemCount + 1}`);
        newItem.find('.rating').val(5);
        
        // Update names
        newItem.find('[name]').each(function() {
            const name = $(this).attr('name');
            $(this).attr('name', name.replace(/\[\d+\]/, `[${itemCount}]`));
        });
        
        repeater.find('.glint-repeater-item').last().after(newItem);
    });
    
    // Remove slide (with last item check)
    $('.glint-repeater').on('click', '.remove-slide', function(e) {
        e.preventDefault();
        const items = $(this).closest('.glint-repeater').find('.glint-repeater-item');
        if (items.length > 1) {
            $(this).closest('.glint-repeater-item').remove();
            // Renumber slides
            items.each(function(index) {
                $(this).find('h4').text(
                    $(this).closest('.review').length ? 
                    `Review #${index + 1}` : `Slide #${index + 1}`
                );
            });
        } else {
            //alert('At least one slide is required');
        }
    });
    
    // Product term autocomplete
    function setupAutocomplete() {
        $('.glint-autocomplete').each(function() {
            const $input = $(this);
            const type = $input.data('type');
            
            $input.autocomplete({
                source: function(request, response) {
                    // Extract the current term (last part after comma)
                    const terms = $input.val().split(',');
                    const lastTerm = terms.pop().trim();
                    
                    if (lastTerm.length < 2) return response([]);
                    
                    $.ajax({
                        url: glint_swiper.ajax_url,
                        data: {
                            action: 'glint_search_product_terms',
                            nonce: glint_swiper.nonce,
                            term: lastTerm,
                            type: type
                        },
                        success: function(results) {
                            response(results);
                        }
                    });
                },
                minLength: 2,
                focus: function() {
                    // Prevent value change on focus
                    return false;
                },
                select: function(event, ui) {
                    const terms = $input.val().split(',');
                    terms.pop(); // Remove current partial term
                    terms.push(ui.item.value);
                    $input.val(terms.join(', ') + ', ');
                    
                    // Close dropdown and prevent default
                    $(this).autocomplete('close');
                    return false;
                }
            }).autocomplete('instance')._renderItem = function(ul, item) {
                // Highlight matching text
                const term = this.term.trim();
                const regex = new RegExp("(" + term + ")", "gi");
                const highlighted = item.label.replace(regex, "<strong>$1</strong>");
                
                return $('<li>')
                    .append('<div>' + highlighted + '</div>')
                    .appendTo(ul);
            };
        });
    }
    
    // Initialize autocomplete when product fields are shown
    $(document).on('change', '#slider_type', function() {
        if ($(this).val() === 'product') {
            setupAutocomplete();
        }
    });
    
    // Initialize autocomplete on page load if product is selected
    if ($('#slider_type').val() === 'product') {
        setupAutocomplete();
    }
});