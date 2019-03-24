import React, { Component } from 'react';
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { library } from '@fortawesome/fontawesome-svg-core';
import { faSpinner } from '@fortawesome/free-solid-svg-icons';

library.add(faSpinner);

class Loading extends Component {
    render = () => {
        return (
            <section className="col-md-24 p-0 mb-4 text-center" key='loading'>
                <h2 className="form-title col-md-24 text-center d-inline-block mt-5 mb-5">Loading</h2>
                <FontAwesomeIcon icon='spinner' spin size='4x'/>
            </section>
        );
    }
}

export default Loading;