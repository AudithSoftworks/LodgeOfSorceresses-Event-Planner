import { noAttendances } from "../../../fixtures/xhr-operations/attendances/no-attendances";
import { content } from "../../../fixtures/xhr-operations/content";
import { sets } from "../../../fixtures/xhr-operations/sets";
import { skills } from "../../../fixtures/xhr-operations/skills";
import { teams } from "../../../fixtures/xhr-operations/teams";
import { users } from "../../../fixtures/xhr-operations/users";
import { stubFetchingUserHeiims } from "../../../fixtures/xhr-operations/users/6/member";
import { noCharacters } from "../../../fixtures/xhr-operations/users/@me/characters/no-characters";
import { stubSoulshrivenWithNoForumOauth } from "../../../fixtures/xhr-operations/users/@me/soulshriven";

describe('Roster Screen for Soulshriven user', function () {
    it('visits Roster page', function () {
        cy.server();
        stubSoulshrivenWithNoForumOauth(cy);
        sets(cy);
        skills(cy);
        content(cy);
        teams(cy);
        noCharacters(cy);
        noAttendances(cy);

        cy.visit('/');
        cy.get('h2[data-cy="loading"]').contains('Checking session...');
        cy.wait('@loadSoulshrivenWithNoForumOauth')

        cy.wait(['@loadCharacters', '@loadSets', '@loadSkills', '@loadContent', '@loadTeams']);
        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/@me');
        cy.request('/api/attendances/347');
        cy.wait('@loadAttendancesForUser347');
        cy.get('h2').should('have.text', 'Welcome, SoulshrivenEsoId!');

        users(cy);
        cy.contains('Roster').click();
        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/users');
        cy.request('GET', '/api/users');
        cy.get('h2[data-cy="loading"]').should('have.text', 'Fetching roster information...');
        cy.wait('@loadUsers');
        cy.get('h2').should('have.text', 'Roster');
        cy.get('ul.roster > li').should('have.length', 34);

        cy.get('section:nth-of-type(1) > h3').should('have.text', 'Members (25)');
        cy.get('section:nth-of-type(1) > ul.roster > li').should('have.length', 25);

        cy.get('section:nth-of-type(2) > h3').should('have.text', 'Soulshriven (9)');
        cy.get('section:nth-of-type(2) > ul.roster > li').should('have.length', 9);

        stubFetchingUserHeiims(cy);
        cy.contains('@HEIIMS').click();
        cy.request('/api/users/6');
        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/users/6');
        cy.get('h2[data-cy="loading"]').should('have.text', 'Fetching user information...');
        cy.wait('@loadHeiims');
        cy.get('h2').should('have.text', '@HEIIMS');
        cy.get('dl:nth-of-type(1)').should('have.class', 'members');
        cy.get('dl:nth-of-type(2)').should('have.class', 'danger');
        cy.get('dl:nth-of-type(3)').should('have.class', 'info');
        cy.get('dl:nth-of-type(4)').should('have.class', '');
        cy.get('table.character-list-table > tbody > tr').should('have.length', 5);
        cy.get('table.character-list-table > tbody > tr.no-clearance').should('have.length', 1);
        cy.get('table.character-list-table > tbody > tr.tier-4').should('have.length', 4);
        cy.get('table.character-list-table > tbody > tr:first-of-type > td > ul.action-list').should('be.visible');
    });
});
