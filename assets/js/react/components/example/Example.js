/*
  To inject this component into the DOM with Twig, you can use the following code snippet:
  {{ spawnComponent('Example', {'foo': 'Bar'}) }}
  The component has to be declared in the assets/js/react/components.js file.
*/

const Example = (props) => {
  return (
    <div>
      <h3>React Component Example</h3>
      <p>Foo: {props.foo}</p>
    </div>
  );
};

export default Example;