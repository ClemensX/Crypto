package de.fehrprice.net.test;


import static org.junit.jupiter.api.Assertions.assertEquals;
import static org.junit.jupiter.api.Assertions.assertTrue;

import java.io.BufferedReader;
import java.io.FileReader;
import java.io.IOException;

import org.junit.jupiter.api.AfterAll;
import org.junit.jupiter.api.BeforeAll;
import org.junit.jupiter.api.Test;

import de.fehrprice.crypto.AES;
import de.fehrprice.crypto.Conv;
import de.fehrprice.crypto.Curve25519;
import de.fehrprice.crypto.Ed25519;
import de.fehrprice.crypto.RandomSeed;
import de.fehrprice.net.ECConnection;

/**
 * test data here: http://ed25519.cr.yp.to/software.html
 *
 */
/**
 * Test Secure communication over HTTP
 *
 */
public class CommTest {
	
    public static boolean disableLongRunningTest = true;
	
	private static Ed25519 ed;
	private static Curve25519 x;
	private static AES aes;
	
	@BeforeAll
	public static void setUp() throws Exception {
		x = new Curve25519();
		ed = new Ed25519();
		aes = new AES();
		aes.setSeed(RandomSeed.createSeed());
	}

	@AfterAll
	public static void tearDown() throws Exception {
	}

	/**
	 * Test unsigned message exchange - no protection against man-in-the-middle-attacks 
	 */
	@Test
	void testECDH() {
		String uBasePoint     = "0900000000000000000000000000000000000000000000000000000000000000";
		String alicePrivate = Conv.toString(aes.random(32));
		String bobPrivate = Conv.toString(aes.random(32));
		
		String alicePublic = x.x25519(alicePrivate, uBasePoint);
		String bobPublic = x.x25519(bobPrivate, uBasePoint);

		// Alice acts as client and calls bob:
		ECConnection comm = new ECConnection(x, ed, aes);
		String sessionKeyAlice = comm.initiateECDH(alicePrivate, bobPublic);
		
		// Bob receives the request:
		ECConnection comm2 = new ECConnection(x, ed, aes);
		String sessionKeybob = comm2.initiateECDH(bobPrivate, alicePublic);
		assertEquals(sessionKeybob, sessionKeyAlice);
	}
	
	@Test
	void testECDSA() {
		String alicePrivate = Conv.toString(aes.random(32));
		String bobPrivate = Conv.toString(aes.random(32));

		String alicePublic = ed.publicKey(alicePrivate);
		String bobPublic = ed.publicKey(bobPrivate);
		
		// Alice acts as client and calls bob:
		ECConnection comm = new ECConnection(x, ed, aes);
		String message = comm.initiateECDSA(alicePrivate, alicePublic, "Alice");
		System.out.println("transfer message: " + message);
	}
}

