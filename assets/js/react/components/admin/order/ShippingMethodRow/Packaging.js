const Packaging = ({ packaging, onChange }) => {
    console.log("Packaging component rendered with packaging:", packaging);
    return (
        <div className="packaging-row">
        <h3>Packaging</h3>
            <div className="packaging-details">
                <h4>Packaging applied :</h4>
                <p>3 x [3 magnums, 42x25x18]</p>
            </div>
        </div>
    );
};

export default Packaging;