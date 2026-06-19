with open('resources/views/layouts/partials/_bell.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace('transform:translate(30%,-30%);', 'transform:translate(40%,-40%); z-index: 10;')

with open('resources/views/layouts/partials/_bell.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)
