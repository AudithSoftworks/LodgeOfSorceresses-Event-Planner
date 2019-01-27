import '../sass/style.scss';

import React from 'react';
import ReactDOM from 'react-dom';
import { BrowserRouter as Router } from "react-router-dom";
import Application from './Components/Application';

const routing = (
    <Router>
        <Application/>
    </Router>
);

ReactDOM.render(routing, document.getElementById('root'));
