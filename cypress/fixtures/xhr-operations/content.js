export const content = cy => {
    cy.fixture('.content.json').as('content');
    cy.route('GET', '/api/content', '@content');
};
