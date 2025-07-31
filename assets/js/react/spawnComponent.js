const { createRoot, createElement } = wp.element;

window.spawnComponent = (componentName, props) => {
  const Component = window.hgesComponents[componentName];
  if (!Component) {
    console.error(`Component ${componentName} not found.`);
    return;
  }

  const root = document.getElementById(`react-component-${componentName}`);
  if (root) {
    createRoot(root).render(createElement(Component, props));
  } else {
    console.error(`Root element for ${componentName} not found.`);
  }
};