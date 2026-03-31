import os
import re

def apply_base_architecture():
    base_dir = r'c:\Users\hp\Downloads\messaging\src\Utopia\Messaging'
    
    # 1. Update Message.php
    message_path = os.path.join(base_dir, 'Message.php')
    with open(message_path, 'r') as f:
        content = f.read()
    if 'public function getTo(): array;' not in content:
        content = content.replace('interface Message\n{', 'interface Message\n{\n    public function getTo(): array;\n\n    public function getAttachments(): ?array;')
    with open(message_path, 'w') as f:
        f.write(content)

    # 2. Update Adapter.php
    adapter_path = os.path.join(base_dir, 'Adapter.php')
    with open(adapter_path, 'r') as f:
        content = f.read()
    
    # Simple search and replace for the send function
    old_send_pattern = r'    public function send\(Message \$message\): array\s+\{.*?\s+    \}'
    new_send = (
        '    public function send(Message $message): array\n'
        '    {\n'
        '        if (!\\is_a($message, $this->getMessageType())) {\n'
        '            throw new Exception(\'Invalid message type.\');\n'
        '        }\n\n'
        '        if (\\count($message->getTo()) > $this->getMaxMessagesPerRequest()) {\n'
        '            throw new Exception(\'Too many messages for this adapter.\');\n'
        '        }\n\n'
        '        return $this->process($message);\n'
        '    }'
    )
    # Using re.sub with escaped backslashes for the replacement
    content = re.sub(old_send_pattern, new_send.replace('\\', '\\\\'), content, flags=re.DOTALL)

    if 'abstract protected function process(Message $message): array;' not in content:
        content = content.replace('    abstract public function getName(): string;', '    abstract public function getName(): string;\n\n    abstract protected function process(Message $message): array;')
    
    if 'use Exception;' not in content:
        content = content.replace('namespace Utopia\\Messaging;', 'namespace Utopia\\Messaging;\n\nuse Exception;')
    
    with open(adapter_path, 'w') as f:
        f.write(content)

    # 3. Update Discord message
    discord_msg_path = os.path.join(base_dir, 'Messages', 'Discord.php')
    with open(discord_msg_path, 'r') as f:
        content = f.read()
    if 'public function getTo(): array' not in content:
        content = content.replace('class Discord implements Message\n{', 'class Discord implements Message\n{\n    public function getTo(): array\n    {\n        return [];\n    }\n')
    with open(discord_msg_path, 'w') as f:
        f.write(content)

    # 4. Update core categorization adapters
    for sub in ['Email.php', 'SMS.php', 'Push.php']:
        path = os.path.join(base_dir, 'Adapter', sub)
        with open(path, 'r') as f:
            content = f.read()
        content = re.sub(
            r'abstract protected function process\(([^)]*)\)(: array)?;?',
            r'abstract protected function process(Message $message): array;',
            content
        )
        with open(path, 'w') as f:
            f.write(content)

    # 5. Update all concrete adapters
    adapter_dir = os.path.join(base_dir, 'Adapter')
    for root, dirs, files in os.walk(adapter_dir):
        for file in files:
            if not file.endswith('.php') or file in ['Email.php', 'SMS.php', 'Push.php']:
                continue
            
            fpath = os.path.join(root, file)
            with open(fpath, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
            
            if 'use Utopia\\Messaging\\Message;' not in content:
                content = re.sub(r'(namespace .*?;)', r'\1\n\nuse Utopia\\Messaging\\Message;', content, count=1)
            
            match = re.search(r'MESSAGE_TYPE = ([\w]+)::class;', content)
            if not match:
                if 'EmailMessage' in content: msg_class = 'EmailMessage'
                elif 'SMSMessage' in content: msg_class = 'SMSMessage'
                elif 'PushMessage' in content: msg_class = 'PushMessage'
                elif 'DiscordMessage' in content: msg_class = 'DiscordMessage'
                else: continue
            else:
                msg_class = match.group(1)

            content = re.sub(
                r'protected function process\(([^)]*)\)(: array)?\s+\{',
                f'protected function process(Message $message): array\n    {{\n        /** @var {msg_class} $message */',
                content
            )
            with open(fpath, 'w', encoding='utf-8') as f:
                f.write(content)

if __name__ == "__main__":
    apply_base_architecture()
