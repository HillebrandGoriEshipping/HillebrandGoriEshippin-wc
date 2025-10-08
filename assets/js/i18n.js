const translate = (key) => {
    const messages = window.hges.i18n.messages;
    return messages[key] || key;
};

window.hges.i18n.translate = translate;