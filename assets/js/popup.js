// KingOfPeace Books - Custom Popup System

class Popup {
  constructor() {
    this.overlay = null;
    this.container = null;
    this.currentPopup = null;
    this.init();
  }

  init() {
    // Create popup elements once
    this.createPopupElements();
    
    // Add CSS to head
    this.addStyles();
    
    // Handle escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && this.isActive()) {
        this.close();
      }
    });
  }

  createPopupElements() {
    this.overlay = document.createElement('div');
    this.overlay.className = 'popup-overlay';
    
    this.overlay.innerHTML = `
      <div class="popup-container">
        <div class="popup-header">
          <div class="popup-title">
            <div class="popup-icon"></div>
            <span class="popup-title-text"></span>
          </div>
          <button class="popup-close" aria-label="Close">&times;</button>
        </div>
        <div class="popup-body">
          <div class="popup-message"></div>
        </div>
        <div class="popup-footer"></div>
      </div>
    `;

    document.body.appendChild(this.overlay);

    // Add event listeners
    this.overlay.querySelector('.popup-close').addEventListener('click', () => this.close());
    this.overlay.addEventListener('click', (e) => {
      if (e.target === this.overlay) {
        this.close();
      }
    });
  }

  addStyles() {
    if (document.getElementById('popup-styles')) return;
    
    const link = document.createElement('link');
    link.id = 'popup-styles';
    link.rel = 'stylesheet';
    link.href = window.BASE_URL + '/assets/css/popup.css';
    document.head.appendChild(link);
  }

  show(options = {}) {
    const {
      type = 'info',
      title = 'KingOfPeace Books',
      message = '',
      buttons = [],
      icon = null
    } = options;

    // Set popup type
    this.overlay.className = `popup-overlay ${type}`;

    // Set title
    const titleElement = this.overlay.querySelector('.popup-title-text');
    titleElement.textContent = title;

    // Set icon
    const iconElement = this.overlay.querySelector('.popup-icon');
    iconElement.innerHTML = this.getIcon(type, icon);

    // Set message
    const messageElement = this.overlay.querySelector('.popup-message');
    messageElement.innerHTML = message;

    // Set buttons
    const footerElement = this.overlay.querySelector('.popup-footer');
    footerElement.innerHTML = '';

    if (buttons.length === 0) {
      // Default OK button
      this.addButton('OK', 'primary', () => this.close());
    } else {
      buttons.forEach(button => {
        this.addButton(button.text, button.style || 'secondary', button.onClick);
      });
    }

    // Show popup
    this.overlay.classList.add('active');
    this.currentPopup = options;

    // Focus first button
    const firstButton = footerElement.querySelector('.popup-button');
    if (firstButton) {
      setTimeout(() => firstButton.focus(), 100);
    }
  }

  addButton(text, style, onClick) {
    const footerElement = this.overlay.querySelector('.popup-footer');
    const button = document.createElement('button');
    button.className = `popup-button popup-button-${style}`;
    button.textContent = text;
    button.addEventListener('click', onClick);
    footerElement.appendChild(button);
    return button;
  }

  getIcon(type, customIcon) {
    if (customIcon) return customIcon;

    const icons = {
      success: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>',
      error: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
      warning: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
      info: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
      confirm: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 12h8M12 8v8"/></svg>'
    };

    return icons[type] || icons.info;
  }

  close() {
    if (!this.isActive()) return;

    this.overlay.classList.add('removing');
    
    setTimeout(() => {
      this.overlay.classList.remove('active', 'removing');
      this.currentPopup = null;
    }, 300);
  }

  isActive() {
    return this.overlay.classList.contains('active');
  }

  // Static methods for easy access
  static success(message, title = 'Success') {
    return window.popup.show({ type: 'success', title, message });
  }

  static error(message, title = 'Error') {
    return window.popup.show({ type: 'error', title, message });
  }

  static warning(message, title = 'Warning') {
    return window.popup.show({ type: 'warning', title, message });
  }

  static info(message, title = 'Information') {
    return window.popup.show({ type: 'info', title, message });
  }

  static confirm(message, onConfirm, onCancel = null, title = 'Confirm') {
    return window.popup.show({
      type: 'confirm',
      title,
      message,
      buttons: [
        {
          text: 'Cancel',
          style: 'secondary',
          onClick: () => {
            window.popup.close();
            if (onCancel) onCancel();
          }
        },
        {
          text: 'Confirm',
          style: 'danger',
          onClick: () => {
            window.popup.close();
            if (onConfirm) onConfirm();
          }
        }
      ]
    });
  }

  static custom(options) {
    return window.popup.show(options);
  }
}

// Replace default alert, confirm, and prompt
window.alert = function(message, title = 'Alert') {
  Popup.info(message, title);
};

window.confirm = function(message, callback = null, title = 'Confirm') {
  Popup.confirm(message, callback, null, title);
  return false; // Always return false for compatibility
};

// Initialize popup system
document.addEventListener('DOMContentLoaded', () => {
  window.popup = new Popup();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
  module.exports = Popup;
}
