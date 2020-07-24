export const sets = cy => {
    cy.fixture('.sets.json').as('sets');
    cy.route('GET', '/api/sets', '@sets');
};
